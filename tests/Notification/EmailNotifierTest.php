<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Notification;

use PHPUnit\Framework\TestCase;
use SlavaAds\Lead\Lead;
use SlavaAds\Lead\QuizDecoder;
use SlavaAds\Notification\EmailNotifier;
use SlavaAds\Notification\LeadMessageFormatter;
use SlavaAds\Tests\Support\FakeMailer;

final class EmailNotifierTest extends TestCase
{
    private function formatter(): LeadMessageFormatter
    {
        return new LeadMessageFormatter(new QuizDecoder());
    }

    public function testSendsPlainTextEmail(): void
    {
        $mailer = new FakeMailer();
        $notifier = new EmailNotifier($mailer, $this->formatter(), 'leads@example.com');

        $result = $notifier->notify((new Lead(name: 'Иван', phone: '+79990001122'))->withId(5));

        self::assertTrue($result);
        self::assertCount(1, $mailer->sent);
        self::assertSame('leads@example.com', $mailer->sent[0]['to']);
        self::assertStringContainsString('Новая заявка №5', $mailer->sent[0]['subject']);
        self::assertStringContainsString('Имя: Иван', $mailer->sent[0]['body']);
    }

    public function testSkippedWhenRecipientEmpty(): void
    {
        $mailer = new FakeMailer();
        $notifier = new EmailNotifier($mailer, $this->formatter(), '');

        self::assertFalse($notifier->notify(new Lead(name: 'Иван', phone: '+79990001122')));
        self::assertCount(0, $mailer->sent);
    }
}
