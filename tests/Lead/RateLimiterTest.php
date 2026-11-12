<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Lead;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SlavaAds\Lead\LeadRepository;
use SlavaAds\Lead\RateLimiter;
use SlavaAds\Tests\Support\FrozenClock;
use SlavaAds\Tests\Support\TestDatabase;

final class RateLimiterTest extends TestCase
{
    public function testAllowsWhenUnderLimits(): void
    {
        $pdo = TestDatabase::create();
        $clock = new FrozenClock(new DateTimeImmutable('2026-06-20 12:00:00'));
        $limiter = new RateLimiter(new LeadRepository($pdo), $clock, perMinute: 3, perHour: 15, perDay: 100);

        self::assertTrue($limiter->isAllowed('1.1.1.1'));
    }

    public function testBlocksWhenPerMinuteExceeded(): void
    {
        $pdo = TestDatabase::create();
        TestDatabase::insertAt($pdo, '1.1.1.1', '2026-06-20 11:59:30');
        TestDatabase::insertAt($pdo, '1.1.1.1', '2026-06-20 11:59:40');
        TestDatabase::insertAt($pdo, '1.1.1.1', '2026-06-20 11:59:50');

        $clock = new FrozenClock(new DateTimeImmutable('2026-06-20 12:00:00'));
        $limiter = new RateLimiter(new LeadRepository($pdo), $clock, perMinute: 3, perHour: 15, perDay: 100);

        self::assertFalse($limiter->isAllowed('1.1.1.1'));
    }

    public function testBlocksWhenPerHourExceeded(): void
    {
        $pdo = TestDatabase::create();
        for ($i = 0; $i < 15; $i++) {
            TestDatabase::insertAt($pdo, '1.1.1.1', '2026-06-20 11:' . str_pad((string) $i, 2, '0', STR_PAD_LEFT) . ':00');
        }

        $clock = new FrozenClock(new DateTimeImmutable('2026-06-20 12:00:00'));
        $limiter = new RateLimiter(new LeadRepository($pdo), $clock, perMinute: 100, perHour: 15, perDay: 100);

        self::assertFalse($limiter->isAllowed('1.1.1.1'));
    }

    public function testDifferentIpIsIndependent(): void
    {
        $pdo = TestDatabase::create();
        TestDatabase::insertAt($pdo, '1.1.1.1', '2026-06-20 11:59:30');
        TestDatabase::insertAt($pdo, '1.1.1.1', '2026-06-20 11:59:40');
        TestDatabase::insertAt($pdo, '1.1.1.1', '2026-06-20 11:59:50');

        $clock = new FrozenClock(new DateTimeImmutable('2026-06-20 12:00:00'));
        $limiter = new RateLimiter(new LeadRepository($pdo), $clock, perMinute: 3, perHour: 15, perDay: 100);

        self::assertTrue($limiter->isAllowed('2.2.2.2'));
    }

    public function testBlocksWhenPerDayExceeded(): void
    {
        $pdo = TestDatabase::create();
        for ($i = 0; $i < 5; $i++) {
            TestDatabase::insertAt($pdo, '1.1.1.1', '2026-06-20 08:' . str_pad((string) ($i * 10), 2, '0', STR_PAD_LEFT) . ':00');
        }

        $clock = new FrozenClock(new DateTimeImmutable('2026-06-20 12:00:00'));
        $limiter = new RateLimiter(new LeadRepository($pdo), $clock, perMinute: 100, perHour: 100, perDay: 5);

        self::assertFalse($limiter->isAllowed('1.1.1.1'));
    }
}
