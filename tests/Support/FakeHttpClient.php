<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Support;

use SlavaAds\Http\HttpClient;

/**
 * Records outbound requests instead of sending them.
 */
final class FakeHttpClient implements HttpClient
{
    /** @var list<array{url:string, fields:array<string, scalar>}> */
    public array $calls = [];

    public function __construct(private readonly bool $result = true)
    {
    }

    public function postForm(string $url, array $fields): bool
    {
        $this->calls[] = ['url' => $url, 'fields' => $fields];
        return $this->result;
    }
}
