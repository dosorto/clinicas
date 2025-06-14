<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NacionalidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lista de nacionalidades que vamos a insertar
        $nacionalidades = [
            'Hondureña',
            'Salvadoreña',
            'Guatemalteca',
            'Nicaragüense',
            'Costarricense',
            'Panameña',
            'Mexicana',
            'Colombiana',
            'Venezolana',
            'Argentina',
            'Peruana',
            'Brasileña',
            'Chilena',
            'Uruguaya',
            'Paraguaya',
            'Estadounidense',
            'Canadiense',
            'Española'
        ];

        // Insertar cada nacionalidad en la tabla, una por una
        foreach ($nacionalidades as $nombre) {
            DB::table('nacionalidades')->insert([
                'nacionalidad' => $nombre,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'created_by' => 1, // ID del usuario que creó esto, puedes cambiarlo dinámicamente si lo necesitas
                'updated_by' => 1,
            ]);
        }
    }
}
