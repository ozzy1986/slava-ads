<?php

declare(strict_types=1);

namespace SlavaAds\Lead;

use SlavaAds\Support\Clock;

/**
 * Per-IP submission throttling. Time comes from the Clock so limits are
 * deterministic in tests.
 */
final class RateLimiter
{
    public function __construct(
        private readonly LeadCounter $counter,
        private readonly Clock $clock,
        private readonly int $perMinute = 2,
        private readonly int $perHour = 5,
        private readonly int $perDay = 5,
    ) {
    }

    public function isAllowed(string $ip): bool
    {
        $now = $this->clock->now();

        $lastMinute = $this->counter->countByIpSince($ip, $now->modify('-1 minute'));
        if ($lastMinute >= $this->perMinute) {
            return false;
        }

        $lastHour = $this->counter->countByIpSince($ip, $now->modify('-1 hour'));
        if ($lastHour >= $this->perHour) {
            return false;
        }

        $lastDay = $this->counter->countByIpSince($ip, $now->modify('-1 day'));

        return $lastDay < $this->perDay;
    }
}
