<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Centros_Medico;

class CentrosMedicoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $centro = Centros_Medico::create([
            'nombre_centro' => 'Hospital SL',
            'direccion' => 'Choluteca',
            'telefono' => '123',
            'rtn' => '123456789',
        ]);

        // El observer debería crear el tenant automáticamente, pero por si acaso
        if (!\App\Models\Tenant::where('centro_id', $centro->id)->exists()) {
            \App\Models\Tenant::create([
                'centro_id' => $centro->id,
                'name' => $centro->nombre_centro,
                'domain' => 'centro' . $centro->id . '.localhost',
                'database' => 'clinica_' . $centro->id,
            ]);
        }
    }
}
