<?php

declare(strict_types=1);

namespace SlavaAds\Http;

/**
 * Immutable view over an HTTP request. Built from superglobals in
 * production and from plain arrays in tests (no global state).
 */
final class Request
{
    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $server
     * @param bool $trustProxyHeaders Only enable behind a trusted reverse proxy
     *                                (Cloudflare/nginx). Direct Apache must keep
     *                                this false: otherwise X-Forwarded-For is
     *                                client-controlled and rate limits are useless.
     */
    public function __construct(
        private readonly string $method,
        private readonly array $post,
        private readonly array $server = [],
        private readonly bool $trustProxyHeaders = false,
    ) {
    }

    public static function fromGlobals(bool $trustProxyHeaders = false): self
    {
        return new self(
            strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')),
            $_POST,
            $_SERVER,
            $trustProxyHeaders,
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    /**
     * Trimmed, NUL-stripped, length-capped field value.
     */
    public function input(string $key, int $maxLength = 255): string
    {
        $value = isset($this->post[$key]) ? trim((string) $this->post[$key]) : '';
        $value = str_replace("\0", '', $value);
        return mb_substr($value, 0, $maxLength);
    }

    /** True when a field is present and non-empty (used for honeypot checks). */
    public function filled(string $key): bool
    {
        return isset($this->post[$key]) && trim((string) $this->post[$key]) !== '';
    }

    public function clientIp(): string
    {
        if ($this->trustProxyHeaders) {
            foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR'] as $key) {
                $raw = $this->server[$key] ?? '';
                if ($raw === '') {
                    continue;
                }
                $candidate = trim(explode(',', (string) $raw)[0]);
                if (self::isValidIp($candidate)) {
                    return $candidate;
                }
            }
        }

        $remote = trim((string) ($this->server['REMOTE_ADDR'] ?? ''));
        return self::isValidIp($remote) ? $remote : '0.0.0.0';
    }

    public function userAgent(): string
    {
        return mb_substr((string) ($this->server['HTTP_USER_AGENT'] ?? ''), 0, 255);
    }

    /**
     * Public host of the request without the port, lowercased.
     */
    public function host(): string
    {
        $raw = (string) ($this->server['HTTP_HOST'] ?? $this->server['SERVER_NAME'] ?? '');
        $host = parse_url('http://' . $raw, PHP_URL_HOST);

        return is_string($host) ? strtolower($host) : '';
    }

    /**
     * Browsers send Origin (or at least Referer) on a same-origin POST.
     * Rejecting cross-site/absent headers blocks naive form-spam scripts
     * that POST directly to the endpoint.
     */
    public function isSameOrigin(): bool
    {
        $source = (string) ($this->server['HTTP_ORIGIN'] ?? $this->server['HTTP_REFERER'] ?? '');
        if ($source === '') {
            return false;
        }

        $host = parse_url($source, PHP_URL_HOST);
        $expected = $this->host();

        return is_string($host) && $expected !== '' && strtolower($host) === $expected;
    }

    private static function isValidIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }
}
