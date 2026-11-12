<?php

declare(strict_types=1);

namespace SlavaAds\Mail;

use SlavaAds\Config\Env;

/**
 * Immutable SMTP credentials/options.
 */
final class SmtpSettings
{
    public function __construct(
        public readonly string $host,
        public readonly int $port,
        public readonly string $username,
        public readonly string $password,
        public readonly string $secure,
        public readonly string $fromEmail,
        public readonly string $fromName,
    ) {
    }

    public static function fromEnv(Env $env): self
    {
        return new self(
            host: $env->get('SMTP_HOST'),
            port: $env->int('SMTP_PORT', 587),
            username: $env->get('SMTP_USER'),
            password: $env->get('SMTP_PASS'),
            secure: strtolower($env->get('SMTP_SECURE', 'tls')),
            fromEmail: $env->get('EMAIL_FROM', $env->get('SMTP_USER')),
            fromName: $env->get('EMAIL_FROM_NAME', 'slava-ads.ru'),
        );
    }

    public function isConfigured(): bool
    {
        return $this->host !== '' && $this->username !== '' && $this->password !== '';
    }
}
