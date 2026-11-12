<?php

declare(strict_types=1);

namespace SlavaAds\Mail;

/**
 * Sends a plain-text email. Implementations decide the transport
 * (SMTP relay, native mail, ...).
 */
interface Mailer
{
    public function send(string $to, string $subject, string $body): bool;
}
