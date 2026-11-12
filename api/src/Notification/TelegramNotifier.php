<?php

declare(strict_types=1);

namespace SlavaAds\Notification;

use SlavaAds\Http\HttpClient;
use SlavaAds\Lead\Lead;

/**
 * Sends the lead to a Telegram chat via the Bot API. Plain, emoji-free
 * formatting with bold field labels.
 */
final class TelegramNotifier implements Notifier
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly LeadMessageFormatter $formatter,
        private readonly string $botToken,
        private readonly string $chatId,
    ) {
    }

    public function notify(Lead $lead): bool
    {
        if ($this->botToken === '' || $this->chatId === '') {
            return false;
        }

        $lines = ['<b>' . $this->escape($this->formatter->title($lead)) . '</b>', ''];
        foreach ($this->formatter->fields($lead) as $label => $value) {
            $lines[] = '<b>' . $this->escape($label) . ':</b> ' . $this->escape($value);
        }

        $url = 'https://api.telegram.org/bot' . $this->botToken . '/sendMessage';

        return $this->http->postForm($url, [
            'chat_id' => $this->chatId,
            'text' => implode("\n", $lines),
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => 'true',
        ]);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
