<?php

declare(strict_types=1);

namespace SlavaAds\Notification;

use SlavaAds\Lead\Lead;
use Throwable;

/**
 * Fans a lead out to every channel (Open/Closed: add channels without
 * touching the controller). Isolates failures so one broken channel never
 * affects the others or the user response.
 */
final class CompositeNotifier implements Notifier
{
    /** @var list<Notifier> */
    private array $channels;

    public function __construct(Notifier ...$channels)
    {
        $this->channels = array_values($channels);
    }

    public function notify(Lead $lead): bool
    {
        $allDelivered = true;
        foreach ($this->channels as $channel) {
            try {
                $delivered = $channel->notify($lead);
            } catch (Throwable $e) {
                error_log('[notify] ' . $channel::class . ' failed: ' . $e->getMessage());
                $delivered = false;
            }
            $allDelivered = $allDelivered && $delivered;
        }

        return $allDelivered;
    }
}
