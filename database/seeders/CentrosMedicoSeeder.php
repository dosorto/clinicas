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

        \App\Models\Tenant::firstOrCreate([
            'centro_id' => $centro->id,
            'name' => $centro->nombre_centro,
            'domain' => 'centro' . $centro->id . '.localhost',
            'database' => 'shared', // o puedes dejarlo null si no lo usas
        ]);
    }
}
