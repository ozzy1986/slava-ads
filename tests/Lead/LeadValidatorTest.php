<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Lead;

use PHPUnit\Framework\TestCase;
use SlavaAds\Lead\Lead;
use SlavaAds\Lead\LeadValidator;

final class LeadValidatorTest extends TestCase
{
    private LeadValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new LeadValidator();
    }

    public function testValidLeadHasNoErrors(): void
    {
        $lead = new Lead(name: 'Иван', phone: '+79990001122');
        self::assertSame([], $this->validator->validate($lead));
        self::assertTrue($this->validator->isValid($lead));
    }

    public function testShortNameRejected(): void
    {
        $lead = new Lead(name: 'И', phone: '+79990001122');
        self::assertContains('Введите имя', $this->validator->validate($lead));
    }

    public function testShortPhoneRejected(): void
    {
        $lead = new Lead(name: 'Иван', phone: '123');
        self::assertContains('Введите телефон или контакт', $this->validator->validate($lead));
    }

    public function testWhitespaceOnlyPhoneRejected(): void
    {
        $lead = new Lead(name: 'Иван', phone: '   ');
        self::assertFalse($this->validator->isValid($lead));
    }
}
