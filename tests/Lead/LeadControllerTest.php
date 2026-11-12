<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Lead;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SlavaAds\Http\Request;
use SlavaAds\Lead\CaptchaVerifier;
use SlavaAds\Lead\FormToken;
use SlavaAds\Lead\LeadController;
use SlavaAds\Lead\LeadFactory;
use SlavaAds\Lead\LeadValidator;
use SlavaAds\Lead\RateLimiter;
use SlavaAds\Tests\Support\FakeCaptchaVerifier;
use SlavaAds\Tests\Support\FrozenClock;
use SlavaAds\Tests\Support\InMemoryLeadStore;
use SlavaAds\Tests\Support\RecordingNotifier;

final class LeadControllerTest extends TestCase
{
    private const SECRET = 'test-form-secret';
    private const NOW = '2026-06-20 12:00:00';

    private function controller(
        InMemoryLeadStore $store,
        RecordingNotifier $notifier,
        int $perMinute = 3,
        ?FormToken $formToken = null,
        ?CaptchaVerifier $captcha = null,
        array $blockedIps = [],
    ): LeadController {
        $clock = new FrozenClock(new DateTimeImmutable(self::NOW));

        return new LeadController(
            new LeadFactory(),
            new LeadValidator(),
            new RateLimiter($store, $clock, $perMinute, 15, 100),
            $store,
            $notifier,
            $formToken ?? new FormToken(self::SECRET, $clock),
            $captcha,
            $blockedIps,
        );
    }

    private function token(): string
    {
        // Issued one minute before the controller's frozen clock (age >= 1s).
        return (new FormToken(
            self::SECRET,
            new FrozenClock(new DateTimeImmutable('2026-06-20 11:59:00')),
        ))->issue();
    }

    /** @param array<string, mixed> $fields */
    private function post(array $fields): Request
    {
        return new Request('POST', $fields + ['form_token' => $this->token()], [
            'REMOTE_ADDR' => '1.2.3.4',
            'HTTP_HOST' => 'slava-ads.ru',
            'HTTP_ORIGIN' => 'https://slava-ads.ru',
        ]);
    }

    private function validPost(): Request
    {
        return $this->post(['name' => 'Иван', 'phone' => '+79990001122']);
    }

    public function testRejectsNonPost(): void
    {
        $response = $this->controller(new InMemoryLeadStore(), new RecordingNotifier())
            ->handle(new Request('GET', []));

        self::assertSame(405, $response->status);
        self::assertFalse($response->body['ok']);
    }

    public function testHoneypotAcceptedButNotStored(): void
    {
        $store = new InMemoryLeadStore();
        $notifier = new RecordingNotifier();

        $response = $this->controller($store, $notifier)->handle(
            $this->post(['name' => 'Иван', 'phone' => '+79990001122', 'company_site' => 'spam'])
        );

        self::assertSame(200, $response->status);
        self::assertTrue($response->body['ok']);
        self::assertCount(0, $store->saved);
        self::assertCount(0, $notifier->received);
    }

    public function testValidationError(): void
    {
        $response = $this->controller(new InMemoryLeadStore(), new RecordingNotifier())
            ->handle($this->post(['name' => 'И', 'phone' => '1']));

        self::assertSame(422, $response->status);
        self::assertSame('Введите имя', $response->body['error']);
    }

    public function testRejectsForeignOrigin(): void
    {
        $store = new InMemoryLeadStore();
        $request = new Request('POST', [
            'name' => 'Иван',
            'phone' => '+79990001122',
            'form_token' => $this->token(),
        ], [
            'REMOTE_ADDR' => '1.2.3.4',
            'HTTP_HOST' => 'slava-ads.ru',
            'HTTP_ORIGIN' => 'https://evil.example',
        ]);

        $response = $this->controller($store, new RecordingNotifier())->handle($request);

        self::assertSame(403, $response->status);
        self::assertCount(0, $store->saved);
    }

    public function testRejectsInvalidFormToken(): void
    {
        $store = new InMemoryLeadStore();
        $request = new Request('POST', [
            'name' => 'Иван',
            'phone' => '+79990001122',
            'form_token' => 'not-a-token',
        ], [
            'REMOTE_ADDR' => '1.2.3.4',
            'HTTP_HOST' => 'slava-ads.ru',
            'HTTP_ORIGIN' => 'https://slava-ads.ru',
        ]);

        $response = $this->controller($store, new RecordingNotifier())->handle($request);

        self::assertSame(403, $response->status);
        self::assertCount(0, $store->saved);
    }

    public function testEmptySecretDisablesFormToken(): void
    {
        $clock = new FrozenClock(new DateTimeImmutable(self::NOW));
        $request = new Request('POST', [
            'name' => 'Иван',
            'phone' => '+79990001122',
        ], [
            'REMOTE_ADDR' => '1.2.3.4',
            'HTTP_HOST' => 'slava-ads.ru',
            'HTTP_ORIGIN' => 'https://slava-ads.ru',
        ]);

        $response = $this->controller(
            new InMemoryLeadStore(),
            new RecordingNotifier(),
            formToken: new FormToken('', $clock),
        )->handle($request);

        self::assertSame(200, $response->status);
    }

    public function testBlockedIpAcceptedSilently(): void
    {
        $store = new InMemoryLeadStore();
        $notifier = new RecordingNotifier();

        $response = $this->controller($store, $notifier, blockedIps: ['1.2.3.4'])
            ->handle($this->validPost());

        self::assertSame(200, $response->status);
        self::assertTrue($response->body['ok']);
        self::assertCount(0, $store->saved);
        self::assertCount(0, $notifier->received);
    }

    public function testRateLimited(): void
    {
        $store = new InMemoryLeadStore(countResult: 99);
        $response = $this->controller($store, new RecordingNotifier())->handle($this->validPost());

        self::assertSame(429, $response->status);
        self::assertCount(0, $store->saved);
    }

    public function testCaptchaFailure(): void
    {
        $store = new InMemoryLeadStore();
        $captcha = new FakeCaptchaVerifier(false);

        $response = $this->controller($store, new RecordingNotifier(), captcha: $captcha)
            ->handle($this->post([
                'name' => 'Иван',
                'phone' => '+79990001122',
                'captcha_answer' => 'WRONG',
                'captcha_token' => 'token-1',
            ]));

        self::assertSame(422, $response->status);
        self::assertSame('captcha', $response->body['code']);
        self::assertCount(0, $store->saved);
        self::assertCount(1, $captcha->calls);
        self::assertSame('WRONG', $captcha->calls[0]['answer']);
        self::assertSame('token-1', $captcha->calls[0]['token']);
    }

    public function testCaptchaSuccess(): void
    {
        $store = new InMemoryLeadStore();
        $captcha = new FakeCaptchaVerifier(true);

        $response = $this->controller($store, new RecordingNotifier(), captcha: $captcha)
            ->handle($this->post([
                'name' => 'Иван',
                'phone' => '+79990001122',
                'captcha_answer' => 'ABCDE',
                'captcha_token' => 'token-2',
            ]));

        self::assertSame(200, $response->status);
        self::assertCount(1, $store->saved);
        self::assertSame('ABCDE', $captcha->calls[0]['answer']);
        self::assertSame('token-2', $captcha->calls[0]['token']);
    }

    public function testPersistFailureReturns500(): void
    {
        $store = new InMemoryLeadStore(throwOnSave: true);
        $response = $this->controller($store, new RecordingNotifier())->handle($this->validPost());

        self::assertSame(500, $response->status);
    }

    public function testSuccessStoresAndNotifies(): void
    {
        $store = new InMemoryLeadStore();
        $notifier = new RecordingNotifier();

        $response = $this->controller($store, $notifier)->handle($this->validPost());

        self::assertSame(200, $response->status);
        self::assertTrue($response->body['ok']);
        self::assertCount(1, $store->saved);
        self::assertCount(1, $notifier->received);
        // Notified lead carries the assigned id.
        self::assertSame(1, $notifier->received[0]->id);
    }

    public function testNotifierFailureDoesNotBreakResponse(): void
    {
        $store = new InMemoryLeadStore();
        $notifier = new RecordingNotifier(throws: true);

        $response = $this->controller($store, $notifier)->handle($this->validPost());

        // Lead saved, response still OK even though notification threw.
        self::assertSame(200, $response->status);
        self::assertCount(1, $store->saved);
    }
}
