<?php

declare(strict_types=1);

namespace SlavaAds\Lead;

use Closure;
use SlavaAds\Support\Clock;

/**
 * Self-hosted image CAPTCHA.
 *
 * The server draws a short code into a PNG (SVG fallback) and returns a
 * signed token with its expiry. The answer itself never travels in the token:
 * verification recomputes the HMAC from the submitted answer. This keeps the
 * check stateless (no sessions, no database, no external services).
 */
final class SimpleCaptcha implements CaptchaVerifier
{
    private const ALPHABET = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    private const CODE_LENGTH = 5;
    private const TTL_SECONDS = 600;
    private const CLOCK_SKEW_SECONDS = 60;

    /**
     * @param (Closure(): string)|null $codeGenerator Test seam; production uses random_int().
     * @param string|null $fontPath Optional TTF override; auto-detected when null.
     */
    public function __construct(
        private readonly string $secret,
        private readonly Clock $clock,
        private readonly ?Closure $codeGenerator = null,
        private readonly ?string $fontPath = null,
    ) {
    }

    /**
     * Build a fresh challenge.
     *
     * @return array{token: string, image: string}
     */
    public function issue(): array
    {
        if ($this->secret === '') {
            return ['token' => '', 'image' => ''];
        }

        $code = $this->generateCode();
        $expires = $this->clock->now()->getTimestamp() + self::TTL_SECONDS;
        $nonce = bin2hex(random_bytes(8));
        $payload = $expires . '.' . $nonce;

        return [
            'token' => $payload . '.' . $this->sign($payload, $code),
            'image' => $this->render($code),
        ];
    }

    public function verify(string $answer, string $token): bool
    {
        if ($this->secret === '' || $answer === '' || $token === '') {
            return false;
        }

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        [$expires, $nonce, $signature] = $parts;
        if (!ctype_digit($expires) || strlen($nonce) !== 16 || !ctype_xdigit($nonce)) {
            return false;
        }

        $now = $this->clock->now()->getTimestamp();
        $expiresAt = (int) $expires;
        if ($expiresAt < $now || $expiresAt > $now + self::TTL_SECONDS + self::CLOCK_SKEW_SECONDS) {
            return false;
        }

        $expected = $this->sign($expires . '.' . $nonce, $answer);

        return hash_equals($expected, $signature);
    }

    private function sign(string $payload, string $answer): string
    {
        return hash_hmac('sha256', $payload . '|' . $this->normalize($answer), $this->secret);
    }

    private function normalize(string $value): string
    {
        $letters = preg_replace('/[^A-Za-z0-9]/', '', $value) ?? '';

        return strtoupper($letters);
    }

    private function generateCode(): string
    {
        if ($this->codeGenerator !== null) {
            return (string) ($this->codeGenerator)();
        }

        $max = strlen(self::ALPHABET) - 1;
        $code = '';
        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $code .= self::ALPHABET[random_int(0, $max)];
        }

        return $code;
    }

    /**
     * Prefer a PNG rendered with GD + FreeType: the code is not machine-
     * readable markup. Fall back to SVG when GD or a TTF font is unavailable.
     */
    private function render(string $code): string
    {
        $font = $this->fontPath ?? $this->resolveFont();
        if ($font !== null && function_exists('imagettftext') && function_exists('imagecreatetruecolor')) {
            $png = $this->renderPng($code, $font);
            if ($png !== null) {
                return $png;
            }
        }

        return $this->renderSvg($code);
    }

    private function resolveFont(): ?string
    {
        $candidates = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSansMono-Bold.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSansMono.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationMono-Bold.ttf',
            'C:\\Windows\\Fonts\\consolab.ttf',
            'C:\\Windows\\Fonts\\consola.ttf',
            'C:\\Windows\\Fonts\\arialbd.ttf',
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function renderPng(string $code, string $font): ?string
    {
        $width = 200;
        $height = 64;
        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            return null;
        }

        $palette = [[17, 24, 39], [29, 78, 216], [15, 118, 110], [124, 58, 237], [180, 83, 9]];
        $background = imagecolorallocate($image, 243, 244, 246);
        imagefilledrectangle($image, 0, 0, $width, $height, $background);

        for ($i = 0; $i < 5; $i++) {
            [$r, $g, $b] = $palette[random_int(0, count($palette) - 1)];
            $color = imagecolorallocatealpha($image, $r, $g, $b, 70);
            imageline(
                $image,
                random_int(0, $width),
                random_int(0, $height),
                random_int(0, $width),
                random_int(0, $height),
                $color,
            );
        }

        for ($i = 0; $i < 140; $i++) {
            [$r, $g, $b] = $palette[random_int(0, count($palette) - 1)];
            $color = imagecolorallocatealpha($image, $r, $g, $b, 55);
            imagesetpixel($image, random_int(0, $width - 1), random_int(0, $height - 1), $color);
        }

        $length = strlen($code);
        $step = (int) floor(($width - 28) / max(1, $length));
        $centerX = (int) round(($width - $step * ($length - 1)) / 2);

        for ($i = 0; $i < $length; $i++) {
            $size = random_int(26, 32);
            $angle = random_int(-16, 16);
            $y = random_int((int) ($height * 0.62), (int) ($height * 0.80));
            [$r, $g, $b] = $palette[random_int(0, count($palette) - 1)];
            $color = imagecolorallocate($image, $r, $g, $b);

            $box = imagettfbbox($size, 0, $font, $code[$i]);
            $charWidth = $box === false ? $size : abs($box[2] - $box[0]);
            $x = $centerX - (int) round($charWidth / 2);

            if (imagettftext($image, $size, $angle, $x, $y, $color, $font, $code[$i]) === false) {
                return null;
            }

            $centerX += $step;
        }

        ob_start();
        imagepng($image);
        $png = ob_get_clean();

        if (!is_string($png) || $png === '') {
            return null;
        }

        return 'data:image/png;base64,' . base64_encode($png);
    }

    /**
     * Fallback used only when GD/FreeType is unavailable. Kept intentionally
     * simple; production servers are expected to have GD.
     */
    private function renderSvg(string $code): string
    {
        $width = 200;
        $height = 64;
        $palette = ['#111827', '#1d4ed8', '#0f766e', '#7c3aed', '#b45309'];
        $svg = [];

        $svg[] = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">',
            $width,
            $height,
            $width,
            $height,
        );
        $svg[] = sprintf('<rect width="%d" height="%d" rx="12" fill="#f3f4f6"/>', $width, $height);

        for ($i = 0; $i < 5; $i++) {
            $svg[] = sprintf(
                '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="%s" stroke-width="%d" opacity="0.25"/>',
                random_int(0, $width),
                random_int(0, $height),
                random_int(0, $width),
                random_int(0, $height),
                $palette[random_int(0, count($palette) - 1)],
                random_int(1, 2),
            );
        }

        for ($i = 0; $i < 18; $i++) {
            $svg[] = sprintf(
                '<circle cx="%d" cy="%d" r="%d" fill="%s" opacity="0.3"/>',
                random_int(0, $width),
                random_int(0, $height),
                random_int(1, 2),
                $palette[random_int(0, count($palette) - 1)],
            );
        }

        $length = strlen($code);
        $step = (int) floor(($width - 28) / max(1, $length));
        $x = (int) round(($width - $step * ($length - 1)) / 2);

        for ($i = 0; $i < $length; $i++) {
            $y = random_int((int) ($height * 0.58), (int) ($height * 0.72));
            $size = random_int(28, 34);
            $angle = random_int(-16, 16);
            $svg[] = sprintf(
                '<text x="%d" y="%d" text-anchor="middle" font-family="DejaVu Sans Mono, Consolas, monospace" font-size="%d" font-weight="700" fill="%s" transform="rotate(%d %d %d)">%s</text>',
                $x,
                $y,
                $size,
                $palette[random_int(0, count($palette) - 1)],
                $angle,
                $x,
                $y,
                $code[$i],
            );
            $x += $step;
        }

        $svg[] = '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode(implode('', $svg));
    }
}
