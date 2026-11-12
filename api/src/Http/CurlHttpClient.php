<?php

declare(strict_types=1);

namespace SlavaAds\Http;

/**
 * cURL-based HTTP client with a stream fallback when ext-curl is absent.
 */
final class CurlHttpClient implements HttpClient
{
    public function __construct(private readonly int $timeoutSeconds = 8)
    {
    }

    public function postForm(string $url, array $fields): bool
    {
        return $this->request($url, http_build_query($fields)) !== null;
    }

    private function request(string $url, string $payload): ?string
    {
        if (function_exists('curl_init')) {
            return $this->requestWithCurl($url, $payload);
        }

        return $this->requestWithStream($url, $payload);
    }

    private function requestWithCurl(string $url, string $payload): ?string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
        ]);
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $failed = curl_errno($ch) !== 0;
        if ($failed) {
            error_log('[http] curl error: ' . curl_error($ch));
        }
        curl_close($ch);

        if ($failed || $status < 200 || $status >= 300 || !is_string($body)) {
            return null;
        }

        return $body;
    }

    private function requestWithStream(string $url, string $payload): ?string
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $payload,
                'timeout' => $this->timeoutSeconds,
                'ignore_errors' => true,
            ],
        ]);
        $result = @file_get_contents($url, false, $context);
        if (!is_string($result)) {
            return null;
        }

        $statusLine = $http_response_header[0] ?? '';
        if (preg_match('/\s(\d{3})\s/', $statusLine, $matches) === 1) {
            $status = (int) $matches[1];
            if ($status < 200 || $status >= 300) {
                return null;
            }
        }

        return $result;
    }
}
