<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Support;

use DateTimeImmutable;
use SlavaAds\Support\Clock;

/**
 * Deterministic clock for tests.
 */
final class FrozenClock implements Clock
{
    public function __construct(private readonly DateTimeImmutable $now)
    {
    }

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }
}
