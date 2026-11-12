<?php

declare(strict_types=1);

namespace SlavaAds;

use PDO;
use SlavaAds\Config\Env;
use SlavaAds\Http\CurlHttpClient;
use SlavaAds\Lead\FormToken;
use SlavaAds\Lead\LeadController;
use SlavaAds\Lead\LeadFactory;
use SlavaAds\Lead\LeadRepository;
use SlavaAds\Lead\LeadValidator;
use SlavaAds\Lead\QuizDecoder;
use SlavaAds\Lead\RateLimiter;
use SlavaAds\Lead\SimpleCaptcha;
use SlavaAds\Mail\Mailer;
use SlavaAds\Mail\NativeMailer;
use SlavaAds\Mail\SmtpMailer;
use SlavaAds\Mail\SmtpSettings;
use SlavaAds\Notification\CompositeNotifier;
use SlavaAds\Notification\EmailNotifier;
use SlavaAds\Notification\LeadMessageFormatter;
use SlavaAds\Notification\Notifier;
use SlavaAds\Notification\TelegramNotifier;
use SlavaAds\Support\SystemClock;

/**
 * Composition root. Wires concrete implementations together so the rest of
 * the codebase depends only on abstractions.
 */
final class App
{
    public static function leadController(Env $env, PDO $pdo): LeadController
    {
        $repository = new LeadRepository($pdo);
        $formatter = new LeadMessageFormatter(new QuizDecoder());

        return new LeadController(
            new LeadFactory(),
            new LeadValidator(),
            new RateLimiter(
                $repository,
                new SystemClock(),
                $env->int('RATE_PER_MINUTE', 2),
                $env->int('RATE_PER_HOUR', 5),
                $env->int('RATE_PER_DAY', 5),
            ),
            $repository,
            self::notifier($env, $formatter),
            self::formToken($env),
            self::captcha($env),
            self::blockedIps($env),
        );
    }

    public static function formToken(Env $env): FormToken
    {
        return new FormToken(
            $env->get('FORM_SECRET', $env->get('ADMIN_PASS_HASH')),
            new SystemClock(),
        );
    }

    public static function captcha(Env $env): ?SimpleCaptcha
    {
        $secret = $env->get('CAPTCHA_SECRET', $env->get('FORM_SECRET', $env->get('ADMIN_PASS_HASH')));
        if ($secret === '') {
            return null;
        }

        return new SimpleCaptcha($secret, new SystemClock());
    }

    public static function notifier(Env $env, LeadMessageFormatter $formatter): Notifier
    {
        return new CompositeNotifier(
            new TelegramNotifier(
                new CurlHttpClient(),
                $formatter,
                $env->get('TELEGRAM_TOKEN'),
                $env->get('TELEGRAM_CHAT_ID'),
            ),
            new EmailNotifier(
                self::mailer($env),
                $formatter,
                $env->get('LEAD_EMAIL'),
            ),
        );
    }

    public static function mailer(Env $env): Mailer
    {
        $smtp = SmtpSettings::fromEnv($env);
        if ($smtp->isConfigured()) {
            return new SmtpMailer($smtp);
        }

        return new NativeMailer($env->get('EMAIL_FROM', 'leads@slava-ads.ru'));
    }

    /** @return list<string> */
    private static function blockedIps(Env $env): array
    {
        $raw = $env->get('BLOCKED_IPS');
        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }
}
