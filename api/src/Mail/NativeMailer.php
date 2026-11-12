<?php

declare(strict_types=1);

namespace SlavaAds\Mail;

/**
 * Fallback transport using PHP's mail(). Used when SMTP is not configured.
 */
final class NativeMailer implements Mailer
{
    public function __construct(private readonly string $fromEmail = 'leads@slava-ads.ru')
    {
    }

    public function send(string $to, string $subject, string $body): bool
    {
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $headers = implode("\r\n", [
            'From: ' . $this->fromEmail,
            'Reply-To: ' . $this->fromEmail,
            'Content-Type: text/plain; charset=UTF-8',
            'X-Mailer: slava-ads-lead',
        ]);

        return @mail($to, $encodedSubject, $body, $headers);
    }
}
