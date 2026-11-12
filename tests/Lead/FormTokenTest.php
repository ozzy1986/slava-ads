<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Lead;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SlavaAds\Lead\FormToken;
use SlavaAds\Tests\Support\MutableClock;

final class FormTokenTest extends TestCase
{
    private function clock(): MutableClock
    {
        return new MutableClock(new DateTimeImmutable('2026-06-20 12:00:00'));
    }

    public function testIssuesAndValidatesAfterMinimumAge(): void
    {
        $clock = $this->clock();
        $token = (new FormToken('secret', $clock))->issue();

        self::assertNotSame('', $token);
        self::assertFalse((new FormToken('secret', $clock))->isValid($token), 'Instant submit is rejected');

        $clock->advance('+2 seconds');
        self::assertTrue((new FormToken('secret', $clock))->isValid($token));
    }

    public function testRejectsTamperedToken(): void
    {
        $clock = $this->clock();
        $token = (new FormToken('secret', $clock))->issue();
        $clock->advance('+2 seconds');

        $tampered = substr($token, 0, -1) . (str_ends_with($token, 'a') ? 'b' : 'a');

        self::assertFalse((new FormToken('secret', $clock))->isValid($tampered));
    }

    public function testRejectsTokenSignedWithAnotherSecret(): void
    {
        $clock = $this->clock();
        $token = (new FormToken('secret', $clock))->issue();
        $clock->advance('+2 seconds');

        self::assertFalse((new FormToken('other-secret', $clock))->isValid($token));
    }

    public function testRejectsExpiredToken(): void
    {
        $clock = $this->clock();
        $token = (new FormToken('secret', $clock))->issue();
        $clock->advance('+25 hours');

        self::assertFalse((new FormToken('secret', $clock))->isValid($token));
    }

    public function testRejectsGarbage(): void
    {
        $clock = $this->clock();
        $token = new FormToken('secret', $clock);

        self::assertFalse($token->isValid(''));
        self::assertFalse($token->isValid('abc'));
        self::assertFalse($token->isValid('123'));
        self::assertFalse($token->isValid('123.bad'));
    }

    public function testEmptySecretDisablesCheck(): void
    {
        $clock = $this->clock();
        $token = new FormToken('', $clock);

        self::assertSame('', $token->issue());
        self::assertTrue($token->isValid('anything'));
    }
}
