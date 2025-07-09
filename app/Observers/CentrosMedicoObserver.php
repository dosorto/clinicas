<?php

namespace App\Observers;

use App\Models\Centros_Medico;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class CentrosMedicoObserver
{
    public function created(Centros_Medico $centro)
    {
        try {
            // Usar findOrCreateForCentro que ya maneja todos los campos correctamente
            $tenant = Tenant::findOrCreateForCentro($centro);
            
            // Hacer el tenant actual si no hay otro activo
            if (!Tenant::current()) {
                $tenant->makeCurrent();
            }

            Log::info("Tenant creado correctamente para el centro médico {$centro->nombre_centro}", [
                'centro_id' => $centro->id,
                'tenant_id' => $tenant->id,
                'domain' => $tenant->domain,
                'database' => $tenant->database
            ]);
        } catch (\Exception $e) {
            Log::error("Error al crear tenant para centro médico {$centro->nombre}", [
                'centro_id' => $centro->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
