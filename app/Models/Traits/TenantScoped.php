<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

trait TenantScoped
{
    /**
     * El método "boot" de un trait se ejecuta automáticamente
     * cuando un modelo que lo usa es inicializado.
     */
    protected static function bootTenantScoped()
    {
        // Nos aseguramos de que haya un usuario con sesión iniciada.
        if (Auth::check()) {
            
            // --- INICIO DE LA CORRECCIÓN ---

            /** @var \App\Models\User $user */ // Pista para el editor
            $user = Auth::user();

            // Ahora usamos la variable $user que ya tiene la "pista"
            if (!$user->hasRole('root')) {

            // --- FIN DE LA CORRECCIÓN ---

                // Si se cumplen las condiciones, añadimos un "Global Scope".
                static::addGlobalScope('centros_medicos', function (Builder $builder) use ($user) {
                    // Forzamos la consulta a incluir siempre el empresa_id del usuario.
                    $builder->where('centro_id', $user->centro_id);
                });
            }
        }
    }
}