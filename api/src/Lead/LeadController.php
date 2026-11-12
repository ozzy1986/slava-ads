<?php

declare(strict_types=1);

namespace SlavaAds\Lead;

use SlavaAds\Http\Request;
use SlavaAds\Http\Response;
use SlavaAds\Notification\Notifier;
use Throwable;

/**
 * Orchestrates lead intake. Pure (returns a Response, never echoes) so the
 * whole flow is unit-testable with fakes.
 */
final class LeadController
{
    private const HONEYPOT_FIELD = 'company_site';

    /**
     * @param list<string> $blockedIps
     */
    public function __construct(
        private readonly LeadFactory $factory,
        private readonly LeadValidator $validator,
        private readonly RateLimiter $rateLimiter,
        private readonly LeadStore $store,
        private readonly Notifier $notifier,
        private readonly ?FormToken $formToken = null,
        private readonly ?CaptchaVerifier $captcha = null,
        private readonly array $blockedIps = [],
    ) {
    }

    public function handle(Request $request): Response
    {
        if (!$request->isPost()) {
            return Response::error('Method not allowed', 405);
        }

        $ip = $request->clientIp();

        // Known spam source: accept silently so the bot gets no feedback.
        if (in_array($ip, $this->blockedIps, true)) {
            error_log('[lead] blocked ip=' . $ip);
            return Response::ok();
        }

        // Honeypot: bots fill the hidden field. Accept silently so they
        // get no signal, but never store or notify.
        if ($request->filled(self::HONEYPOT_FIELD)) {
            error_log('[lead] honeypot ip=' . $ip);
            return Response::ok();
        }

        // Browsers send Origin on a same-origin POST; direct curl/spam
        // scripts usually do not. This is cheap and catches naive bots.
        if (!$request->isSameOrigin()) {
            error_log('[lead] bad origin ip=' . $ip);
            return Response::error('Запрос отклонён.', 403);
        }

        // Signed timestamp issued when the form opens: blocks instant POSTs
        // and tokens older than 24 hours. Empty secret disables the check.
        if ($this->formToken !== null && !$this->formToken->isValid($request->input('form_token', 255))) {
            error_log('[lead] bad form token ip=' . $ip);
            return Response::error('Обновите страницу и попробуйте ещё раз.', 403);
        }

        $lead = $this->factory->fromRequest($request);

        $errors = $this->validator->validate($lead);
        if ($errors !== []) {
            return Response::error($errors[0], 422);
        }

        if (!$this->rateLimiter->isAllowed($lead->ip)) {
            error_log('[lead] rate limited ip=' . $lead->ip);
            return Response::error('Слишком много попыток. Попробуйте позже.', 429);
        }

        if (
            $this->captcha !== null
            && !$this->captcha->verify(
                $request->input('captcha_answer', 32),
                $request->input('captcha_token', 512),
            )
        ) {
            error_log('[lead] captcha failed ip=' . $lead->ip);
            return Response::error('Неверный код с картинки. Попробуйте ещё раз.', 422, 'captcha');
        }

        try {
            $id = $this->store->save($lead);
        } catch (Throwable $e) {
            error_log('[lead] persist failed: ' . $e->getMessage());
            return Response::error('Временная ошибка. Попробуйте ещё раз.', 500);
        }

        // Notifications must never break the successful response.
        try {
            $this->notifier->notify($lead->withId($id));
        } catch (Throwable $e) {
            error_log('[lead] notify failed: ' . $e->getMessage());
        }

        return Response::ok();
    }
}
