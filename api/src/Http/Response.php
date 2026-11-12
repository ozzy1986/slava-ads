<?php

declare(strict_types=1);

namespace SlavaAds\Http;

/**
 * Immutable JSON response value object. Returning it (instead of echoing)
 * keeps controllers pure and unit-testable.
 */
final class Response
{
    /** @param array<string, mixed> $body */
    public function __construct(
        public readonly int $status,
        public readonly array $body,
    ) {
    }

    public static function ok(): self
    {
        return new self(200, ['ok' => true]);
    }

    public static function error(string $message, int $status, ?string $code = null): self
    {
        $body = ['ok' => false, 'error' => $message];
        if ($code !== null) {
            $body['code'] = $code;
        }

        return new self($status, $body);
    }
}
