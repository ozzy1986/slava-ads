<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Support;

use SlavaAds\Mail\Mailer;

/**
 * Records sent emails instead of delivering them.
 */
final class FakeMailer implements Mailer
{
    /** @var list<array{to:string, subject:string, body:string}> */
    public array $sent = [];

    public function __construct(private readonly bool $result = true)
    {
    }

    public function send(string $to, string $subject, string $body): bool
    {
        $this->sent[] = ['to' => $to, 'subject' => $subject, 'body' => $body];
        return $this->result;
    }
}
