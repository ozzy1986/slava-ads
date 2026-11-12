<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Mail;

use PHPUnit\Framework\TestCase;
use SlavaAds\Config\Env;
use SlavaAds\Mail\SmtpSettings;

final class SmtpSettingsTest extends TestCase
{
    public function testFromEnvReadsValues(): void
    {
        $settings = SmtpSettings::fromEnv(new Env([
            'SMTP_HOST' => 'smtp-relay.brevo.com',
            'SMTP_PORT' => '587',
            'SMTP_USER' => 'user@smtp-brevo.com',
            'SMTP_PASS' => 'secret',
            'SMTP_SECURE' => 'TLS',
            'EMAIL_FROM' => 'from@example.com',
            'EMAIL_FROM_NAME' => 'Brand',
        ]));

        self::assertSame('smtp-relay.brevo.com', $settings->host);
        self::assertSame(587, $settings->port);
        self::assertSame('tls', $settings->secure);
        self::assertSame('from@example.com', $settings->fromEmail);
        self::assertSame('Brand', $settings->fromName);
        self::assertTrue($settings->isConfigured());
    }

    public function testNotConfiguredWhenCredentialsMissing(): void
    {
        $settings = SmtpSettings::fromEnv(new Env(['SMTP_HOST' => 'smtp-relay.brevo.com']));
        self::assertFalse($settings->isConfigured());
    }

    public function testFromEmailFallsBackToUser(): void
    {
        $settings = SmtpSettings::fromEnv(new Env([
            'SMTP_HOST' => 'h',
            'SMTP_USER' => 'user@smtp-brevo.com',
            'SMTP_PASS' => 'p',
        ]));
        self::assertSame('user@smtp-brevo.com', $settings->fromEmail);
    }
}
