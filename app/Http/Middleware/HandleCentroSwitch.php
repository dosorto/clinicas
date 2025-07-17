<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Centros_Medico;

class HandleCentroSwitch
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('switch_centro')) {
            $centroId = $request->get('switch_centro');
            $user = auth()->user();
            
            if ($user && $user->canAccessCentro($centroId)) {
                if ($user->switchToTenant($centroId)) {
                    session(['current_centro_id' => $centroId]);
                    
                    // Mostrar mensaje de éxito en la siguiente carga
                    $centro = Centros_Medico::find($centroId);
                    session()->flash('filament.centro_switched', "Centro cambiado a: {$centro->nombre_centro}");
                    
                    // Redireccionar sin el parámetro para limpiar la URL
                    return redirect()->to($request->url());
                }
            }
        }

        return $next($request);
    }
}
