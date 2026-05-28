<?php

namespace App\Providers;

use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\TransactionRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register repository bindings.
     */
    public function register(): void
    {
        $this->app->bind(TransactionRepository::class, function ($app) {
            return new TransactionRepository($app->make(\App\Models\Transaction::class));
        });

        $this->app->bind(EmployeeRepository::class, function ($app) {
            return new EmployeeRepository($app->make(\App\Models\Employee::class));
        });

        $this->app->bind(UserRepository::class, function ($app) {
            return new UserRepository($app->make(\App\Models\User::class));
        });
    }

    public function boot(): void {}
}
