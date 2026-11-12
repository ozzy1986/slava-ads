<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Notification;

use PHPUnit\Framework\TestCase;
use SlavaAds\Lead\Lead;
use SlavaAds\Notification\CompositeNotifier;
use SlavaAds\Tests\Support\RecordingNotifier;

final class CompositeNotifierTest extends TestCase
{
    public function testNotifiesEveryChannel(): void
    {
        $a = new RecordingNotifier();
        $b = new RecordingNotifier();
        $composite = new CompositeNotifier($a, $b);

        $lead = new Lead(name: 'Иван', phone: '+79990001122');
        self::assertTrue($composite->notify($lead));
        self::assertCount(1, $a->received);
        self::assertCount(1, $b->received);
    }

    public function testIsolatesThrowingChannel(): void
    {
        $failing = new RecordingNotifier(throws: true);
        $working = new RecordingNotifier();
        $composite = new CompositeNotifier($failing, $working);

        $result = $composite->notify(new Lead(name: 'Иван', phone: '+79990001122'));

        // One channel threw, but the other still ran; aggregate is false.
        self::assertFalse($result);
        self::assertCount(1, $working->received);
    }

    public function testAggregatesFalseResult(): void
    {
        $composite = new CompositeNotifier(
            new RecordingNotifier(result: true),
            new RecordingNotifier(result: false),
        );

        self::assertFalse($composite->notify(new Lead(name: 'Иван', phone: '+79990001122')));
    }
}
