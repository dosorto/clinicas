<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // ...otras policies...
        \Spatie\Permission\Models\Role::class => \App\Policies\RolePolicy::class,
        \App\Models\Centros_Medico::class => \App\Policies\CentrosMedicoPolicy::class,
        \App\Models\Enfermedades__Paciente::class => \App\Policies\EnfermedadesPacientePolicy::class,
        \App\Filament\Pages\PerfilMedico::class => \App\Policies\PerfilMedicoPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
