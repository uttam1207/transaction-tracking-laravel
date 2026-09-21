<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create a super_admin user who bypasses all service-permission middleware.
     */
    protected function adminUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role'   => 'super_admin',
            'status' => 'active',
        ], $overrides));
    }

    /**
     * Authenticate the test session as a fresh super_admin user.
     */
    protected function asAdmin(): static
    {
        $this->actingAs($this->adminUser());
        return $this;
    }
}
