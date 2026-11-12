<?php

declare(strict_types=1);

namespace SlavaAds\Lead;

use DateTimeImmutable;

/**
 * Read side used for rate limiting. Narrow interface so RateLimiter does
 * not depend on the full repository.
 */
interface LeadCounter
{
    public function countByIpSince(string $ip, DateTimeImmutable $since): int;
}
