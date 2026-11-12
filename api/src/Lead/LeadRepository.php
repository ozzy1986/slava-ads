<?php

declare(strict_types=1);

namespace SlavaAds\Lead;

use DateTimeImmutable;
use PDO;

/**
 * Persistence boundary for leads. Portable SQL (no vendor-specific date
 * math) so the same code runs on MySQL in production and SQLite in tests.
 */
final class LeadRepository implements LeadStore, LeadCounter
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function save(Lead $lead): int
    {
        $utm = $lead->utm->toArray();
        $sql = 'INSERT INTO leads
            (name, phone, message, quiz, source, page, referrer,
             utm_source, utm_medium, utm_campaign, utm_content, utm_term, ip, user_agent)
            VALUES (:name, :phone, :message, :quiz, :source, :page, :referrer,
             :utm_source, :utm_medium, :utm_campaign, :utm_content, :utm_term, :ip, :user_agent)';

        $this->pdo->prepare($sql)->execute([
            'name' => $lead->name,
            'phone' => $lead->phone,
            'message' => $lead->message,
            'quiz' => $lead->quizRaw,
            'source' => $lead->source,
            'page' => $lead->page,
            'referrer' => $lead->referrer,
            'utm_source' => $utm['utm_source'],
            'utm_medium' => $utm['utm_medium'],
            'utm_campaign' => $utm['utm_campaign'],
            'utm_content' => $utm['utm_content'],
            'utm_term' => $utm['utm_term'],
            'ip' => $lead->ip,
            'user_agent' => $lead->userAgent,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function countByIpSince(string $ip, DateTimeImmutable $since): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM leads WHERE ip = :ip AND created_at >= :since'
        );
        $stmt->execute(['ip' => $ip, 'since' => $since->format(self::DATE_FORMAT)]);

        return (int) $stmt->fetchColumn();
    }

    /** @return list<array<string, mixed>> */
    public function recent(int $limit = 500, ?LeadStatus $status = null): array
    {
        if ($status !== null) {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM leads WHERE status = :status ORDER BY created_at DESC LIMIT :limit'
            );
            $stmt->bindValue('status', $status->value);
        } else {
            $stmt = $this->pdo->prepare('SELECT * FROM leads ORDER BY created_at DESC LIMIT :limit');
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return $this->pdo->query('SELECT * FROM leads ORDER BY created_at DESC')->fetchAll();
    }

    public function updateStatus(int $id, LeadStatus $status): void
    {
        $this->pdo->prepare('UPDATE leads SET status = :status WHERE id = :id')
            ->execute(['status' => $status->value, 'id' => $id]);
    }

    /** @return array{total:int,new:int,since:int} */
    public function stats(DateTimeImmutable $since): array
    {
        $total = (int) $this->pdo->query('SELECT COUNT(*) FROM leads')->fetchColumn();

        $new = (int) $this->pdo
            ->query("SELECT COUNT(*) FROM leads WHERE status = 'new'")
            ->fetchColumn();

        $recentStmt = $this->pdo->prepare('SELECT COUNT(*) FROM leads WHERE created_at >= :since');
        $recentStmt->execute(['since' => $since->format(self::DATE_FORMAT)]);

        return [
            'total' => $total,
            'new' => $new,
            'since' => (int) $recentStmt->fetchColumn(),
        ];
    }
}
