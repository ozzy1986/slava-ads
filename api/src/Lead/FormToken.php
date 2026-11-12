<?php

declare(strict_types=1);

namespace SlavaAds\Lead;

use SlavaAds\Support\Clock;

/**
 * Signed, stateless form token. The client gets it when the modal opens and
 * sends it back with the lead. A minimum age blocks instant POST scripts;
 * a maximum age limits token replay. No server-side storage is needed.
 */
final class FormToken
{
    private const MIN_AGE_SECONDS = 1;
    private const MAX_AGE_SECONDS = 86400;

    public function __construct(
        private readonly string $secret,
        private readonly Clock $clock,
    ) {
    }

    public function issue(): string
    {
        if ($this->secret === '') {
            return '';
        }

        $issuedAt = (string) $this->clock->now()->getTimestamp();

        return $issuedAt . '.' . $this->sign($issuedAt);
    }

    public function isValid(string $token): bool
    {
        // No secret configured (local dev / tests) — keep the form usable.
        if ($this->secret === '') {
            return true;
        }

        $parts = explode('.', $token, 2);
        if (count($parts) !== 2 || !ctype_digit($parts[0])) {
            return false;
        }

        [$issuedAt, $signature] = $parts;
        if (!hash_equals($this->sign($issuedAt), $signature)) {
            return false;
        }

        $age = $this->clock->now()->getTimestamp() - (int) $issuedAt;

        return $age >= self::MIN_AGE_SECONDS && $age <= self::MAX_AGE_SECONDS;
    }

    private function sign(string $issuedAt): string
    {
        return hash_hmac('sha256', 'lead-form|' . $issuedAt, $this->secret);
    }
}
