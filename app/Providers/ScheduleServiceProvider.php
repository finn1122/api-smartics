<?php

namespace App\Providers;

use App\Jobs\UpdateProductSupplierStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\ServiceProvider;

class ScheduleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Programa el Job diariamente a las 3 AM
        Schedule::job(new UpdateProductSupplierStatus())
            ->dailyAt('03:00')
            ->onFailure(function (\Throwable $e) {
                Log::error('Error en UpdateProductSupplierStatus: ' . $e->getMessage());
            });
    }
}
