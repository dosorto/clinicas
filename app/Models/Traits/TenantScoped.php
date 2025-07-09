<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Spatie\Multitenancy\Models\Tenant;

trait TenantScoped
{
    /**
     * El método "boot" de un trait se ejecuta automáticamente
     * cuando un modelo que lo usa es inicializado.
     */
    protected static function bootTenantScoped()
    {
        // Asignar centro_id automáticamente al crear
        static::creating(function ($model) {
            if (!$model->centro_id) {
                $model->centro_id = static::getCurrentTenantId();
            }
        });

        // Solo aplicar scope global si no es usuario root
        static::addGlobalScope('centros_medicos', function (Builder $builder) {
            if (!static::shouldBypassTenantScope()) {
                $builder->where('centro_id', static::getCurrentTenantId());
            }
        });
    }

    /**
     * Determina si se debe omitir el scope del tenant
     */
    protected static function shouldBypassTenantScope(): bool
    {
        if (Auth::check() && Auth::user()->hasRole('root')) {
            return true;
        }
        return false;
    }

    /**
     * Obtiene el ID del tenant actual
     */
    protected static function getCurrentTenantId(): ?int
    {
        // Primero intentar obtener del tenant actual de Spatie
        if ($tenant = Tenant::current()) {
            return $tenant->id;
        }

        // Si hay usuario autenticado, usar su centro_id
        if (Auth::check() && !Auth::user()->hasRole('root')) {
            return Auth::user()->centro_id;
        }

        return null;
    }
}