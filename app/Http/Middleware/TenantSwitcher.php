<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Centros_Medico;

class TenantSwitcher
{
    public function handle(Request $request, Closure $next)
    {
        // Si hay un parámetro de centro en la URL o sesión
        $centroId = $request->get('centro_id') ?? session('current_centro_id');
        
        if ($centroId && auth()->check()) {
            $user = auth()->user();
            
            // Verificar si el usuario puede acceder a este centro
            if ($user->canAccessCentro($centroId)) {
                // Cambiar al tenant correspondiente
                $tenant = Tenant::where('centro_id', $centroId)->first();
                if ($tenant) {
                    $tenant->makeCurrent();
                    session(['current_centro_id' => $centroId]);
                }
            }
        } else if (auth()->check()) {
            // Si no hay centro especificado, usar el centro del usuario por defecto
            $user = auth()->user();
            if ($user->centro_id && !$user->hasRole('root')) {
                $user->switchToTenant($user->centro_id);
                session(['current_centro_id' => $user->centro_id]);
            }
        }

        return $next($request);
    }
}
