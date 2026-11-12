<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Lead;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SlavaAds\Lead\SimpleCaptcha;
use SlavaAds\Tests\Support\MutableClock;

final class SimpleCaptchaTest extends TestCase
{
    private function clock(): MutableClock
    {
        return new MutableClock(new DateTimeImmutable('2026-06-20 12:00:00'));
    }

    private function captcha(MutableClock $clock, string $secret = 'captcha-secret'): SimpleCaptcha
    {
        return new SimpleCaptcha($secret, $clock, static fn (): string => 'ABCDE');
    }

    public function testIssuesTokenAndImage(): void
    {
        $issued = $this->captcha($this->clock())->issue();

        self::assertNotSame('', $issued['token']);
        self::assertCount(3, explode('.', $issued['token']));
        self::assertMatchesRegularExpression('~^data:image/(png|svg\+xml);base64,~', $issued['image']);

        $encoded = substr($issued['image'], strpos($issued['image'], ',') + 1);
        $binary = base64_decode($encoded, true);
        self::assertIsString($binary);

        if (str_starts_with($issued['image'], 'data:image/png;base64,')) {
            self::assertStringStartsWith("\x89PNG", $binary);
        } else {
            self::assertStringContainsString('<svg', $binary);
        }
    }

    public function testUsesPngWhenGdAndFontAreAvailable(): void
    {
        if (!function_exists('imagettftext') || !function_exists('imagecreatetruecolor')) {
            self::markTestSkipped('GD/FreeType is not available');
        }

        $font = null;
        foreach ([
            '/usr/share/fonts/truetype/dejavu/DejaVuSansMono-Bold.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSansMono.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationMono-Bold.ttf',
            'C:\\Windows\\Fonts\\consolab.ttf',
            'C:\\Windows\\Fonts\\consola.ttf',
            'C:\\Windows\\Fonts\\arialbd.ttf',
        ] as $path) {
            if (is_file($path)) {
                $font = $path;
                break;
            }
        }

        if ($font === null) {
            self::markTestSkipped('No TTF font found');
        }

        $captcha = new SimpleCaptcha('secret', $this->clock(), static fn (): string => 'ABCDE', $font);

        self::assertStringStartsWith('data:image/png;base64,', $captcha->issue()['image']);
    }

    public function testAcceptsCorrectAnswerIgnoringCaseAndSpaces(): void
    {
        $captcha = $this->captcha($this->clock());
        $token = $captcha->issue()['token'];

        self::assertTrue($captcha->verify('ABCDE', $token));
        self::assertTrue($captcha->verify('abcde', $token));
        self::assertTrue($captcha->verify(' ab cde ', $token));
    }

    public function testRejectsWrongAnswer(): void
    {
        $captcha = $this->captcha($this->clock());
        $token = $captcha->issue()['token'];

        self::assertFalse($captcha->verify('ABCDX', $token));
        self::assertFalse($captcha->verify('', $token));
    }

    public function testRejectsExpiredToken(): void
    {
        $clock = $this->clock();
        $captcha = $this->captcha($clock);
        $token = $captcha->issue()['token'];

        $clock->advance('+11 minutes');

        self::assertFalse($captcha->verify('ABCDE', $token));
    }

    public function testRejectsTamperedToken(): void
    {
        $captcha = $this->captcha($this->clock());
        $token = $captcha->issue()['token'];
        $tampered = substr($token, 0, -1) . (str_ends_with($token, 'a') ? 'b' : 'a');

        self::assertFalse($captcha->verify('ABCDE', $tampered));
        self::assertFalse($captcha->verify('ABCDE', 'not-a-token'));
        self::assertFalse($captcha->verify('ABCDE', ''));
    }

    public function testRejectsTokenSignedWithAnotherSecret(): void
    {
        $clock = $this->clock();
        $token = $this->captcha($clock, 'secret-one')->issue()['token'];

        self::assertFalse($this->captcha($clock, 'secret-two')->verify('ABCDE', $token));
    }

    public function testEmptySecretDisablesCaptcha(): void
    {
        $captcha = $this->captcha($this->clock(), '');

        self::assertSame(['token' => '', 'image' => ''], $captcha->issue());
        self::assertFalse($captcha->verify('ABCDE', 'anything'));
    }
}
