<?php

declare(strict_types=1);

/**
 * Application bootstrap: autoloader + environment. Returns the loaded Env
 * so entry points (lead.php, admin) can build what they need.
 */

require __DIR__ . '/autoload.php';

use SlavaAds\Config\Env;

return Env::fromFile(__DIR__ . '/.env');
