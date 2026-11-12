<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Support;

use DateTimeImmutable;
use RuntimeException;
use SlavaAds\Lead\Lead;
use SlavaAds\Lead\LeadCounter;
use SlavaAds\Lead\LeadStore;

/**
 * In-memory lead store + counter for controller tests (no database).
 */
final class InMemoryLeadStore implements LeadStore, LeadCounter
{
    /** @var list<Lead> */
    public array $saved = [];

    private int $nextId = 1;

    public function __construct(
        private readonly bool $throwOnSave = false,
        private readonly int $countResult = 0,
    ) {
    }

    public function save(Lead $lead): int
    {
        if ($this->throwOnSave) {
            throw new RuntimeException('db down');
        }
        $this->saved[] = $lead;
        return $this->nextId++;
    }

    public function countByIpSince(string $ip, DateTimeImmutable $since): int
    {
        return $this->countResult;
    }
}
