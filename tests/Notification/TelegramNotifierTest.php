<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Notification;

use PHPUnit\Framework\TestCase;
use SlavaAds\Lead\Lead;
use SlavaAds\Lead\QuizDecoder;
use SlavaAds\Notification\LeadMessageFormatter;
use SlavaAds\Notification\TelegramNotifier;
use SlavaAds\Tests\Support\FakeHttpClient;

final class TelegramNotifierTest extends TestCase
{
    private function formatter(): LeadMessageFormatter
    {
        return new LeadMessageFormatter(new QuizDecoder());
    }

    public function testSendsToBotApiWithoutEmoji(): void
    {
        $http = new FakeHttpClient();
        $notifier = new TelegramNotifier($http, $this->formatter(), 'TOKEN', 'CHAT');

        $result = $notifier->notify((new Lead(name: 'Иван', phone: '+79990001122'))->withId(7));

        self::assertTrue($result);
        self::assertCount(1, $http->calls);

        $call = $http->calls[0];
        self::assertSame('https://api.telegram.org/botTOKEN/sendMessage', $call['url']);
        self::assertSame('CHAT', $call['fields']['chat_id']);

        $text = (string) $call['fields']['text'];
        self::assertStringContainsString('Новая заявка №7', $text);
        self::assertStringContainsString('<b>Имя:</b> Иван', $text);
        // No emoji anywhere in the payload.
        self::assertSame(0, preg_match('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $text));
    }

    public function testEscapesHtml(): void
    {
        $http = new FakeHttpClient();
        $notifier = new TelegramNotifier($http, $this->formatter(), 'TOKEN', 'CHAT');

        $notifier->notify((new Lead(name: '<b>x</b>', phone: '+79990001122'))->withId(1));

        $text = (string) $http->calls[0]['fields']['text'];
        self::assertStringContainsString('&lt;b&gt;x&lt;/b&gt;', $text);
    }

    public function testSkippedWhenNotConfigured(): void
    {
        $http = new FakeHttpClient();
        $notifier = new TelegramNotifier($http, $this->formatter(), '', '');

        self::assertFalse($notifier->notify(new Lead(name: 'Иван', phone: '+79990001122')));
        self::assertCount(0, $http->calls);
    }
}
