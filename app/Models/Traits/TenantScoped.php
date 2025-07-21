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
                $tenantId = static::getCurrentTenantId();
                if ($tenantId) {
                    $builder->where('centro_id', $tenantId);
                }
            }
        });
    }

    /**
     * Determina si se debe omitir el scope del tenant
     */
    protected static function shouldBypassTenantScope(): bool
    {
        if (Auth::check() && Auth::user()->hasRole('root')) {
            // Root puede ver todos los datos si no hay un centro específico seleccionado
            return !session('current_centro_id');
        }
        return false;
    }

    /**
     * Obtiene el ID del tenant actual
     */
    protected static function getCurrentTenantId(): ?int
    {
        // Primero verificar si hay un centro seleccionado en la sesión
        if ($centroId = session('current_centro_id')) {
            return $centroId;
        }

        // Luego intentar obtener del tenant actual de Spatie
        if ($tenant = Tenant::current()) {
            return $tenant->centro_id;
        }

        // Si hay usuario autenticado y no es root, usar su centro_id
        if (Auth::check() && !Auth::user()->hasRole('root')) {
            return Auth::user()->centro_id;
        }

        return null;
    }

    /**
     * Scope para filtrar por un centro específico (útil para root)
     */
    public function scopeForCentro($query, $centroId)
    {
        return $query->where('centro_id', $centroId);
    }

    /**
     * Scope para obtener datos de todos los centros (solo root)
     */
    public function scopeAllCentros($query)
    {
        if (Auth::check() && Auth::user()->hasRole('root')) {
            return $query->withoutGlobalScope('centros_medicos');
        }
        return $query;
    }
}