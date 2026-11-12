<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Config;

use PHPUnit\Framework\TestCase;
use SlavaAds\Config\Env;

final class EnvTest extends TestCase
{
    public function testReturnsDefaultForMissingKey(): void
    {
        $env = new Env([]);
        self::assertSame('fallback', $env->get('MISSING', 'fallback'));
    }

    public function testReturnsDefaultForEmptyValue(): void
    {
        $env = new Env(['KEY' => '']);
        self::assertSame('fallback', $env->get('KEY', 'fallback'));
    }

    public function testReadsValue(): void
    {
        $env = new Env(['KEY' => 'value']);
        self::assertSame('value', $env->get('KEY'));
    }

    public function testIntCasting(): void
    {
        $env = new Env(['N' => '42']);
        self::assertSame(42, $env->int('N'));
        self::assertSame(7, $env->int('MISSING', 7));
    }

    public function testHas(): void
    {
        $env = new Env(['A' => 'x', 'B' => '']);
        self::assertTrue($env->has('A'));
        self::assertFalse($env->has('B'));
        self::assertFalse($env->has('C'));
    }

    public function testFromFileParsesCommentsQuotesAndEquals(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($path, <<<ENV
            # comment
            DB_HOST=localhost
            TOKEN="abc=def"
            EMPTY=
            QUOTED='single'
            ENV);

        $env = Env::fromFile($path);
        unlink($path);

        self::assertSame('localhost', $env->get('DB_HOST'));
        self::assertSame('abc=def', $env->get('TOKEN'));
        self::assertSame('single', $env->get('QUOTED'));
        self::assertSame('def', $env->get('EMPTY', 'def'));
    }

    public function testFromMissingFileIsEmpty(): void
    {
        $env = Env::fromFile(__DIR__ . '/does-not-exist.env');
        self::assertFalse($env->has('ANYTHING'));
    }
}
