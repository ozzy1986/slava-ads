<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Lead;

use PHPUnit\Framework\TestCase;
use SlavaAds\Http\Request;
use SlavaAds\Lead\LeadFactory;

final class LeadFactoryTest extends TestCase
{
    public function testBuildsLeadFromRequest(): void
    {
        $request = new Request('POST', [
            'name' => 'Иван',
            'phone' => '+7 900 000-00-00',
            'message' => 'Нужна реклама',
            'quiz' => '{"goal":"leads"}',
            'source' => 'hero',
            'page' => '/',
            'referrer' => 'https://ya.ru',
            'utm_source' => 'yandex',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'brand',
            'utm_content' => 'ad1',
            'utm_term' => 'директолог',
        ], ['REMOTE_ADDR' => '8.8.8.8', 'HTTP_USER_AGENT' => 'UA']);

        $lead = (new LeadFactory())->fromRequest($request);

        self::assertSame('Иван', $lead->name);
        self::assertSame('+7 900 000-00-00', $lead->phone);
        self::assertSame('Нужна реклама', $lead->message);
        self::assertSame('hero', $lead->source);
        self::assertSame('8.8.8.8', $lead->ip);
        self::assertSame('UA', $lead->userAgent);
        self::assertSame('yandex', $lead->utm->source);
        self::assertSame('директолог', $lead->utm->term);
    }

    public function testCapsNameLength(): void
    {
        $request = new Request('POST', ['name' => str_repeat('я', 200)]);
        $lead = (new LeadFactory())->fromRequest($request);
        self::assertSame(120, mb_strlen($lead->name));
    }
}
