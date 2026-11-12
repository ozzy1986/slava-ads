<?php

declare(strict_types=1);

namespace SlavaAds\Lead;

/**
 * Verifies a CAPTCHA answer. Kept as an interface so the controller can be
 * tested without rendering images.
 */
interface CaptchaVerifier
{
    public function verify(string $answer, string $token): bool;
}
