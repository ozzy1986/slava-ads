<?php

declare(strict_types=1);

/**
 * Zero-dependency PSR-4 autoloader for the SlavaAds\ namespace.
 *
 * Production ships no vendor/ directory; this maps SlavaAds\* to api/src/*.
 * Tests use Composer's autoloader, which also resolves this namespace.
 */
spl_autoload_register(static function (string $class): void {
    $prefix = 'SlavaAds\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});
