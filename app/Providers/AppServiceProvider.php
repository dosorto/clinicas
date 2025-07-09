<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Centros_Medico;
use App\Observers\CentrosMedicoObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Centros_Medico::observe(CentrosMedicoObserver::class);
    }
}
