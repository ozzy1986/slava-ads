<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Lead;

use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\TestCase;
use SlavaAds\Lead\Lead;
use SlavaAds\Lead\LeadRepository;
use SlavaAds\Lead\LeadStatus;
use SlavaAds\Lead\Utm;
use SlavaAds\Tests\Support\TestDatabase;

final class LeadRepositoryTest extends TestCase
{
    private PDO $pdo;
    private LeadRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = TestDatabase::create();
        $this->repository = new LeadRepository($this->pdo);
    }

    public function testSaveReturnsIdAndPersistsFields(): void
    {
        $lead = new Lead(
            name: 'Иван',
            phone: '+79990001122',
            message: 'Реклама',
            utm: new Utm(source: 'yandex'),
            ip: '1.2.3.4',
        );

        $id = $this->repository->save($lead);
        self::assertSame(1, $id);

        $row = $this->pdo->query('SELECT * FROM leads WHERE id = 1')->fetch();
        self::assertSame('Иван', $row['name']);
        self::assertSame('yandex', $row['utm_source']);
        self::assertSame('new', $row['status']);
    }

    public function testCountByIpSince(): void
    {
        TestDatabase::insertAt($this->pdo, '1.1.1.1', '2026-06-20 10:00:00');
        TestDatabase::insertAt($this->pdo, '1.1.1.1', '2026-06-20 10:05:00');
        TestDatabase::insertAt($this->pdo, '2.2.2.2', '2026-06-20 10:05:00');

        $since = new DateTimeImmutable('2026-06-20 10:02:00');
        self::assertSame(1, $this->repository->countByIpSince('1.1.1.1', $since));
        self::assertSame(2, $this->repository->countByIpSince('1.1.1.1', new DateTimeImmutable('2026-06-20 09:00:00')));
    }

    public function testRecentFiltersByStatus(): void
    {
        TestDatabase::insertAt($this->pdo, '1.1.1.1', '2026-06-20 10:00:00', 'new');
        TestDatabase::insertAt($this->pdo, '1.1.1.1', '2026-06-20 10:01:00', 'sent');

        self::assertCount(2, $this->repository->recent());
        self::assertCount(1, $this->repository->recent(500, LeadStatus::Sent));
    }

    public function testUpdateStatus(): void
    {
        TestDatabase::insertAt($this->pdo, '1.1.1.1', '2026-06-20 10:00:00', 'new');
        $this->repository->updateStatus(1, LeadStatus::Contacted);

        $row = $this->pdo->query('SELECT status FROM leads WHERE id = 1')->fetch();
        self::assertSame('contacted', $row['status']);
    }

    public function testStats(): void
    {
        TestDatabase::insertAt($this->pdo, '1.1.1.1', (new DateTimeImmutable('now'))->format('Y-m-d H:i:s'), 'new');
        TestDatabase::insertAt($this->pdo, '1.1.1.1', '2020-01-01 00:00:00', 'sent');

        $stats = $this->repository->stats((new DateTimeImmutable('now'))->modify('-7 days'));
        self::assertSame(2, $stats['total']);
        self::assertSame(1, $stats['new']);
        self::assertSame(1, $stats['since']);
    }
}
