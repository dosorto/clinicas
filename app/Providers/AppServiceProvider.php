<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Centros_Medico;
use App\Models\Factura;
use App\Models\ContabilidadMedica\CargoMedico;
use App\Models\Tenant;
use App\Observers\CentrosMedicoObserver;
use App\Observers\FacturaObserver;
use App\Observers\CargoMedicoObserver;
use Illuminate\Support\Facades\Event;
use Spatie\Multitenancy\Events\TenantCreated;

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
        // Observador para centros médicos
        Centros_Medico::observe(CentrosMedicoObserver::class);
        
        // Observadores para contabilidad médica automatizada
        Factura::observe(FacturaObserver::class);
        CargoMedico::observe(CargoMedicoObserver::class);

        // Escuchar eventos de tenant
        Event::listen(TenantCreated::class, function (TenantCreated $event) {
            /** @var Tenant $tenant */
            $tenant = $event->tenant;
            
            if ($tenant->centro_id && !Tenant::current()) {
                $tenant->makeCurrent();
            }
        });
    }
}
