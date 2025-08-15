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
        $centros = [
            [
                'nombre_centro' => 'Hospital San Lucas',
                'direccion' => 'Tegucigalpa',
                'telefono' => '2233-4455',
                'rtn' => '0801199901234'
            ],
            /*[
                'nombre_centro' => 'Clínica Viera',
                'direccion' => 'San Pedro Sula',
                'telefono' => '2541-7896',
                'rtn' => '0801199905678'
            ],
            [
                'nombre_centro' => 'Centro Médico Hondureño',
                'direccion' => 'La Ceiba',
                'telefono' => '2443-1212',
                'rtn' => '0801199909012'
            ],
            [
                'nombre_centro' => 'Hospital del Valle',
                'direccion' => 'Comayagua',
                'telefono' => '2772-3434',
                'rtn' => '0801199903456'
            ],
            [
                'nombre_centro' => 'Clínica Santa María',
                'direccion' => 'Choloma',
                'telefono' => '2555-6767',
                'rtn' => '0801199907890'
            ],
            [
                'nombre_centro' => 'Hospital La Paz',
                'direccion' => 'La Paz',
                'telefono' => '2783-8989',
                'rtn' => '0801199902345'
            ],
            [
                'nombre_centro' => 'Centro Médico San Jorge',
                'direccion' => 'El Progreso',
                'telefono' => '2558-0101',
                'rtn' => '0801199906789'
            ],
            [
                'nombre_centro' => 'Hospital Atlántida',
                'direccion' => 'Tela',
                'telefono' => '2448-2323',
                'rtn' => '0801199901235'
            ],
            [
                'nombre_centro' => 'Clínica San Francisco',
                'direccion' => 'Santa Rosa de Copán',
                'telefono' => '2662-4545',
                'rtn' => '0801199905679'
            ],
            [
                'nombre_centro' => 'Hospital del Sur',
                'direccion' => 'Choluteca',
                'telefono' => '2782-6767',
                'rtn' => '0801199909013'
            ],
            [
                'nombre_centro' => 'Centro Médico Occidental',
                'direccion' => 'Copán Ruinas',
                'telefono' => '2651-8989',
                'rtn' => '0801199903457'
            ],
            [
                'nombre_centro' => 'Hospital San Vicente',
                'direccion' => 'Danlí',
                'telefono' => '2763-1213',
                'rtn' => '0801199907891'
            ],
            [
                'nombre_centro' => 'Clínica Los Andes',
                'direccion' => 'Siguatepeque',
                'telefono' => '2773-3435',
                'rtn' => '0801199902346'
            ],
            [
                'nombre_centro' => 'Hospital San Felipe',
                'direccion' => 'Trujillo',
                'telefono' => '2434-5657',
                'rtn' => '0801199906790'
            ],
            [
                'nombre_centro' => 'Centro Médico Valle de Sula',
                'direccion' => 'Villanueva',
                'telefono' => '2567-7879',
                'rtn' => '0801199901236'
            ],
            [
                'nombre_centro' => 'Hospital San Pedro',
                'direccion' => 'Olanchito',
                'telefono' => '2442-9091',
                'rtn' => '0801199905680'
            ],
            [
                'nombre_centro' => 'Clínica Santa Teresa',
                'direccion' => 'Juticalpa',
                'telefono' => '2792-1214',
                'rtn' => '0801199909014'
            ],
            [
                'nombre_centro' => 'Hospital San Antonio',
                'direccion' => 'Catacamas',
                'telefono' => '2793-3436',
                'rtn' => '0801199903458'
            ],
            [
                'nombre_centro' => 'Centro Médico Pinares',
                'direccion' => 'Santa Bárbara',
                'telefono' => '2643-5658',
                'rtn' => '0801199907892'
            ],
            [
                'nombre_centro' => 'Hospital del Occidente',
                'direccion' => 'Gracias',
                'telefono' => '2656-7870',
                'rtn' => '0801199902347'
            ]*/
        ];

        foreach ($centros as $centroData) {
            $centro = Centros_Medico::create($centroData);

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

        // Asignar el primer centro (Hospital San Lucas) como el centro principal para root
        $principalCentro = Centros_Medico::where('nombre_centro', 'Hospital San Lucas')->first();
        
        if ($principalCentro && !\App\Models\Tenant::where('centro_id', $principalCentro->id)->exists()) {
            \App\Models\Tenant::create([
                'centro_id' => $principalCentro->id,
                'name' => $principalCentro->nombre_centro,
                'domain' => 'centro' . $principalCentro->id . '.localhost',
                'database' => 'clinica_' . $principalCentro->id,
            ]);
        }
    }
}