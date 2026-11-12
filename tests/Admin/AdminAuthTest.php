<?php

declare(strict_types=1);

namespace SlavaAds\Tests\Admin;

use PHPUnit\Framework\TestCase;
use SlavaAds\Admin\AdminAuth;
use SlavaAds\Config\Env;

final class AdminAuthTest extends TestCase
{
    private function auth(string $user = 'admin', string $password = 'secret'): AdminAuth
    {
        return new AdminAuth($user, password_hash($password, PASSWORD_DEFAULT));
    }

    public function testAcceptsValidCredentials(): void
    {
        self::assertTrue($this->auth()->verify('admin', 'secret'));
    }

    public function testRejectsWrongPassword(): void
    {
        self::assertFalse($this->auth()->verify('admin', 'wrong'));
    }

    public function testRejectsWrongUser(): void
    {
        self::assertFalse($this->auth()->verify('root', 'secret'));
    }

    public function testRejectsWhenNoHashConfigured(): void
    {
        $auth = AdminAuth::fromEnv(new Env(['ADMIN_USER' => 'admin']));
        self::assertFalse($auth->verify('admin', 'anything'));
    }
}
