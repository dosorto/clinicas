<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAndSetCentro
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            // Para usuarios root
            if (auth()->user()->hasRole('root')) {
                // Obtener el centro seleccionado de la sesión
                $selectedCentroId = session('current_centro_id');
                
                // Si no hay centro seleccionado y hay centros disponibles, seleccionar el primero
                if (!$selectedCentroId) {
                    $primerCentro = \App\Models\Centros_Medico::first();
                    if ($primerCentro) {
                        $selectedCentroId = $primerCentro->id;
                        session(['current_centro_id' => $selectedCentroId]);
                    }
                }
            }
            // Para usuarios normales
            else {
                // Asegurarse de que siempre usen su centro asignado
                session(['current_centro_id' => auth()->user()->centro_id]);
            }
        }

        return $next($request);
    }
}
