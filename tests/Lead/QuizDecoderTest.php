<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Lead;

use PHPUnit\Framework\TestCase;
use SlavaAds\Lead\QuizDecoder;

final class QuizDecoderTest extends TestCase
{
    private QuizDecoder $decoder;

    protected function setUp(): void
    {
        $this->decoder = new QuizDecoder();
    }

    public function testEmptyReturnsEmpty(): void
    {
        self::assertSame('', $this->decoder->decode(''));
    }

    public function testMapsKnownValues(): void
    {
        $result = $this->decoder->decode('{"goal":"leads","channel":"yandex"}');
        self::assertSame('Больше заявок · Яндекс Директ', $result);
    }

    public function testUnknownValueFallsBackToRaw(): void
    {
        $result = $this->decoder->decode('{"goal":"custom"}');
        self::assertSame('custom', $result);
    }

    public function testInvalidJsonReturnsRaw(): void
    {
        self::assertSame('not-json', $this->decoder->decode('not-json'));
    }
}
