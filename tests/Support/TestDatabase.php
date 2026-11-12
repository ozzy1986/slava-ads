<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Support;

use PDO;
use SlavaAds\Database\Connection;

/**
 * In-memory SQLite database mirroring the production `leads` schema.
 * Lets repository logic be tested without MySQL.
 */
final class TestDatabase
{
    public static function create(): PDO
    {
        $pdo = new PDO('sqlite::memory:', null, null, Connection::options());
        $pdo->exec(
            'CREATE TABLE leads (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                created_at TEXT NOT NULL DEFAULT (datetime(\'now\')),
                name TEXT NOT NULL,
                phone TEXT NOT NULL,
                message TEXT,
                quiz TEXT,
                source TEXT,
                page TEXT,
                referrer TEXT,
                utm_source TEXT,
                utm_medium TEXT,
                utm_campaign TEXT,
                utm_content TEXT,
                utm_term TEXT,
                ip TEXT,
                user_agent TEXT,
                status TEXT NOT NULL DEFAULT \'new\'
            )'
        );

        return $pdo;
    }

    public static function insertAt(PDO $pdo, string $ip, string $createdAt, string $status = 'new'): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO leads (name, phone, ip, created_at, status)
             VALUES (:name, :phone, :ip, :created_at, :status)'
        );
        $stmt->execute([
            'name' => 'Test',
            'phone' => '+70000000000',
            'ip' => $ip,
            'created_at' => $createdAt,
            'status' => $status,
        ]);
    }
}
