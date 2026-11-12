<?php

declare(strict_types=1);

namespace SlavaAds\Notification;

use SlavaAds\Lead\Lead;

/**
 * A channel that delivers a new-lead notification. Implementations must
 * not throw; they return success so one failing channel never blocks
 * others or the HTTP response.
 */
interface Notifier
{
    public function notify(Lead $lead): bool;
}
