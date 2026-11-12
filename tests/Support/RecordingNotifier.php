<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Support;

use RuntimeException;
use SlavaAds\Lead\Lead;
use SlavaAds\Notification\Notifier;

/**
 * Configurable notifier double: records leads, can return false or throw.
 */
final class RecordingNotifier implements Notifier
{
    /** @var list<Lead> */
    public array $received = [];

    public function __construct(
        private readonly bool $result = true,
        private readonly bool $throws = false,
    ) {
    }

    public function notify(Lead $lead): bool
    {
        $this->received[] = $lead;
        if ($this->throws) {
            throw new RuntimeException('channel down');
        }
        return $this->result;
    }
}
