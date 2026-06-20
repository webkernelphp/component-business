<?php

declare(strict_types=1);

namespace Webkernel\Component\Business\Providers;

use Illuminate\Support\ServiceProvider;

class BusinessServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
