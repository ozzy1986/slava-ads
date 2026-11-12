<?php

declare(strict_types=1);

namespace SlavaAds\Admin;

use SlavaAds\Config\Env;

/**
 * Credential check for the admin panel. Constant-time comparisons; refuses
 * access when no password hash is configured.
 */
final class AdminAuth
{
    public function __construct(
        private readonly string $username,
        private readonly string $passwordHash,
    ) {
    }

    public static function fromEnv(Env $env): self
    {
        return new self($env->get('ADMIN_USER', 'admin'), $env->get('ADMIN_PASS_HASH'));
    }

    public function verify(string $username, string $password): bool
    {
        if ($this->passwordHash === '') {
            return false;
        }
        if (!hash_equals($this->username, $username)) {
            return false;
        }

        return password_verify($password, $this->passwordHash);
    }
}
