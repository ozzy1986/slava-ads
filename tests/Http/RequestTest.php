<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Http;

use PHPUnit\Framework\TestCase;
use SlavaAds\Http\Request;

final class RequestTest extends TestCase
{
    public function testInputTrimsAndStripsNul(): void
    {
        $request = new Request('POST', ['name' => "  Иван\0  "]);
        self::assertSame('Иван', $request->input('name'));
    }

    public function testInputCapsLengthMultibyte(): void
    {
        $request = new Request('POST', ['name' => str_repeat('я', 50)]);
        self::assertSame(str_repeat('я', 10), $request->input('name', 10));
    }

    public function testInputMissingKeyReturnsEmpty(): void
    {
        self::assertSame('', (new Request('POST', []))->input('x'));
    }

    public function testIsPost(): void
    {
        self::assertTrue((new Request('POST', []))->isPost());
        self::assertFalse((new Request('GET', []))->isPost());
    }

    public function testFilledDetectsHoneypot(): void
    {
        self::assertTrue((new Request('POST', ['hp' => 'spam']))->filled('hp'));
        self::assertFalse((new Request('POST', ['hp' => '  ']))->filled('hp'));
        self::assertFalse((new Request('POST', []))->filled('hp'));
    }

    public function testClientIpIgnoresSpoofedHeadersByDefault(): void
    {
        $request = new Request('POST', [], [
            'HTTP_CF_CONNECTING_IP' => '1.1.1.1',
            'HTTP_X_FORWARDED_FOR' => '2.2.2.2, 3.3.3.3',
            'REMOTE_ADDR' => '9.9.9.9',
        ]);

        self::assertSame('9.9.9.9', $request->clientIp());
    }

    public function testClientIpUsesProxyHeadersOnlyWhenTrusted(): void
    {
        $cf = new Request('POST', [], [
            'HTTP_CF_CONNECTING_IP' => '1.1.1.1',
            'REMOTE_ADDR' => '9.9.9.9',
        ], true);
        self::assertSame('1.1.1.1', $cf->clientIp());

        $fwd = new Request('POST', [], [
            'HTTP_X_FORWARDED_FOR' => '2.2.2.2, 3.3.3.3',
            'REMOTE_ADDR' => '9.9.9.9',
        ], true);
        self::assertSame('2.2.2.2', $fwd->clientIp());

        $invalid = new Request('POST', [], [
            'HTTP_X_FORWARDED_FOR' => 'not-an-ip',
            'REMOTE_ADDR' => '9.9.9.9',
        ], true);
        self::assertSame('9.9.9.9', $invalid->clientIp());

        self::assertSame('0.0.0.0', (new Request('POST', [], []))->clientIp());
    }

    public function testSameOriginAcceptsMatchingOriginOrReferer(): void
    {
        $origin = new Request('POST', [], [
            'HTTP_HOST' => 'slava-ads.ru',
            'HTTP_ORIGIN' => 'https://slava-ads.ru',
        ]);
        self::assertTrue($origin->isSameOrigin());

        $referer = new Request('POST', [], [
            'HTTP_HOST' => 'slava-ads.ru:443',
            'HTTP_REFERER' => 'https://slava-ads.ru/page?x=1',
        ]);
        self::assertTrue($referer->isSameOrigin());
    }

    public function testSameOriginRejectsMissingOrForeignHeaders(): void
    {
        $missing = new Request('POST', [], ['HTTP_HOST' => 'slava-ads.ru']);
        self::assertFalse($missing->isSameOrigin());

        $foreign = new Request('POST', [], [
            'HTTP_HOST' => 'slava-ads.ru',
            'HTTP_ORIGIN' => 'https://evil.example',
        ]);
        self::assertFalse($foreign->isSameOrigin());

        $nullOrigin = new Request('POST', [], [
            'HTTP_HOST' => 'slava-ads.ru',
            'HTTP_ORIGIN' => 'null',
        ]);
        self::assertFalse($nullOrigin->isSameOrigin());
    }

    public function testUserAgentCapped(): void
    {
        $request = new Request('POST', [], ['HTTP_USER_AGENT' => str_repeat('a', 300)]);
        self::assertSame(255, strlen($request->userAgent()));
    }
}
