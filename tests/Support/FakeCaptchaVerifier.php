<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Support;

use SlavaAds\Lead\CaptchaVerifier;

/**
 * Records CAPTCHA verification attempts without rendering images.
 */
final class FakeCaptchaVerifier implements CaptchaVerifier
{
    /** @var list<array{answer:string, token:string}> */
    public array $calls = [];

    public function __construct(private readonly bool $result = true)
    {
    }

    public function verify(string $answer, string $token): bool
    {
        $this->calls[] = ['answer' => $answer, 'token' => $token];
        return $this->result;
    }
}
