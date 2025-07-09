<?php

namespace App\Observers;

use App\Models\Centros_Medico;
use App\Models\Tenant;

class CentrosMedicoObserver
{
    public function created(Centros_Medico $centro)
    {
        Tenant::create([
            'centro_id' => $centro->id,
            'name' => $centro->nombre_centro,
            'domain' => 'centro' . $centro->id . '.localhost',
            'database' => 'shared', // o null si no usas BD separada
        ]);
    }
}
