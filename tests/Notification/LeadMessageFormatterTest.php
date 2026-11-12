<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Notification;

use PHPUnit\Framework\TestCase;
use SlavaAds\Lead\Lead;
use SlavaAds\Lead\QuizDecoder;
use SlavaAds\Lead\Utm;
use SlavaAds\Notification\LeadMessageFormatter;

final class LeadMessageFormatterTest extends TestCase
{
    private LeadMessageFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new LeadMessageFormatter(new QuizDecoder());
    }

    public function testTitleUsesId(): void
    {
        $lead = (new Lead(name: 'Иван', phone: '+7999'))->withId(42);
        self::assertSame('Новая заявка №42', $this->formatter->title($lead));
    }

    public function testRequiredFieldsAlwaysPresent(): void
    {
        $lead = new Lead(name: 'Иван', phone: '+79990001122');
        $fields = $this->formatter->fields($lead);

        self::assertSame('Иван', $fields['Имя']);
        self::assertSame('+79990001122', $fields['Контакт']);
        self::assertArrayNotHasKey('Задача', $fields);
        self::assertArrayNotHasKey('UTM', $fields);
    }

    public function testOptionalFieldsIncludedWhenPresent(): void
    {
        $lead = new Lead(
            name: 'Иван',
            phone: '+79990001122',
            message: 'Реклама',
            quizRaw: '{"goal":"leads"}',
            source: 'hero',
            page: '/',
            utm: new Utm(source: 'yandex'),
            ip: '1.2.3.4',
        );
        $fields = $this->formatter->fields($lead);

        self::assertSame('Реклама', $fields['Задача']);
        self::assertSame('Больше заявок', $fields['Квиз']);
        self::assertSame('hero', $fields['Блок']);
        self::assertSame('yandex', $fields['UTM']);
        self::assertSame('1.2.3.4', $fields['IP']);
    }
}
