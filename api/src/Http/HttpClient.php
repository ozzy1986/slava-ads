<?php

declare(strict_types=1);

namespace SlavaAds\Http;

/**
 * Minimal outbound HTTP client. Abstracted so notifiers can be tested
 * without real network calls.
 */
interface HttpClient
{
    /**
     * POST url-encoded fields. Returns true on a 2xx response.
     *
     * @param array<string, scalar> $fields
     */
    public function postForm(string $url, array $fields): bool;
}
