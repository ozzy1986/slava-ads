<?php

declare(strict_types=1);

namespace SlavaAds\Support;

use DateTimeImmutable;

/**
 * Abstraction over "now" so time-dependent logic (rate limiting) is
 * deterministic and testable.
 */
interface Clock
{
    public function now(): DateTimeImmutable;
}
