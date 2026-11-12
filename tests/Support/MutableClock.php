<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Support;

use DateTimeImmutable;
use SlavaAds\Support\Clock;

/**
 * Clock that can be advanced in tests.
 */
final class MutableClock implements Clock
{
    public function __construct(private DateTimeImmutable $now)
    {
    }

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }

    public function advance(string $modifier): void
    {
        $this->now = $this->now->modify($modifier);
    }
}
