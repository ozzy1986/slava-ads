<?php

declare(strict_types=1);

namespace SlavaAds\Config;

/**
 * Immutable environment configuration loaded from a .env file.
 *
 * Replaces global env() helpers so configuration can be injected and
 * faked in tests (Dependency Inversion).
 */
final class Env
{
    /** @param array<string, string> $values */
    public function __construct(private readonly array $values = [])
    {
    }

    /**
     * Parse a .env file. Missing files yield an empty config (no errors),
     * so the same code path works in tests and on a fresh server.
     */
    public static function fromFile(string $path): self
    {
        if (!is_file($path)) {
            return new self();
        }

        $values = [];
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }
            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));
            if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'") && $value[strlen($value) - 1] === $value[0]) {
                $value = substr($value, 1, -1);
            }
            $values[$key] = $value;
        }

        return new self($values);
    }

    public function get(string $key, string $default = ''): string
    {
        $value = $this->values[$key] ?? '';
        return $value !== '' ? $value : $default;
    }

    public function int(string $key, int $default = 0): int
    {
        $value = $this->get($key);
        return $value === '' ? $default : (int) $value;
    }

    public function has(string $key): bool
    {
        return ($this->values[$key] ?? '') !== '';
    }
}
