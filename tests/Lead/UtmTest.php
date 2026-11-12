<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Lead;

use PHPUnit\Framework\TestCase;
use SlavaAds\Lead\Utm;

final class UtmTest extends TestCase
{
    public function testEmptyUtm(): void
    {
        $utm = new Utm();
        self::assertTrue($utm->isEmpty());
        self::assertSame('', $utm->toLine());
    }

    public function testToLineSkipsEmptyParts(): void
    {
        $utm = new Utm(source: 'yandex', campaign: 'brand');
        self::assertSame('yandex / brand', $utm->toLine());
        self::assertFalse($utm->isEmpty());
    }

    public function testToArrayKeys(): void
    {
        $utm = new Utm(source: 'g', medium: 'cpc', campaign: 'c', content: 'co', term: 't');
        self::assertSame([
            'utm_source' => 'g',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'c',
            'utm_content' => 'co',
            'utm_term' => 't',
        ], $utm->toArray());
    }
}
