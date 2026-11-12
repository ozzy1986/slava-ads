<?php

declare(strict_types=1);

namespace SlavaAds\Notification;

use SlavaAds\Lead\Lead;
use SlavaAds\Mail\Mailer;

/**
 * Delivers the lead by email using any Mailer transport.
 */
final class EmailNotifier implements Notifier
{
    public function __construct(
        private readonly Mailer $mailer,
        private readonly LeadMessageFormatter $formatter,
        private readonly string $recipient,
    ) {
    }

    public function notify(Lead $lead): bool
    {
        if ($this->recipient === '') {
            return false;
        }

        $lines = [];
        foreach ($this->formatter->fields($lead) as $label => $value) {
            $lines[] = $label . ': ' . $value;
        }
        $body = $this->formatter->title($lead) . " — slava-ads.ru\n\n" . implode("\n", $lines) . "\n";

        $subject = $this->formatter->title($lead) . ' — slava-ads.ru';

        return $this->mailer->send($this->recipient, $subject, $body);
    }
}
