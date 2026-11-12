<?php

declare(strict_types=1);

namespace SlavaAds\Database;

use PDO;
use SlavaAds\Config\Env;

/**
 * Builds a configured PDO instance from environment settings.
 */
final class Connection
{
    public static function fromEnv(Env $env): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $env->get('DB_HOST', 'localhost'),
            $env->get('DB_NAME'),
        );

        return new PDO($dsn, $env->get('DB_USER'), $env->get('DB_PASS'), self::options());
    }

    /** @return array<int, mixed> */
    public static function options(): array
    {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
    }
}
