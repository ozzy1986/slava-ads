<?php

declare(strict_types=1);

namespace SlavaAds\Http;

/**
 * Emits a Response as JSON. The only component that touches PHP output
 * functions, isolating side effects from the testable controller.
 */
final class Responder
{
    public function emit(Response $response): void
    {
        http_response_code($response->status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($response->body, JSON_UNESCAPED_UNICODE);
    }
}
