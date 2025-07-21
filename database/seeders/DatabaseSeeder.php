<?php

namespace Database\Seeders;

use App\Models\Citas;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Crear roles y permisos primero
        $this->call([
            RolesAndPermissionsSeeder::class,
            CentrosMedicoSeeder::class, // Crear centros antes de asignar usuarios
        ]);

        // Crear usuario root después de tener los centros
        User::factory()->create([
            'name' => 'root',
            'email' => 'root@example.com',
        ]);
        
        $user = User::find(1);
        $user->assignRole('root');
        
        // Asignar el primer centro médico (Hospital San Lucas) al usuario root
        $primerCentro = \App\Models\Centros_Medico::where('nombre_centro', 'Hospital San Lucas')->first();
        if ($primerCentro) {
            $user->centro_id = $primerCentro->id;
            $user->save();
        }

        // Continuar con el resto de seeders
        $this->call([
          EspecialidadSeeder::class,
          NacionalidadSeeder::class,
          PersonaSeeder::class,
          //MedicoSeeder::class,
          //EspecialidadMedicoSeeder::class,
          EnfermedadeSeeder::class,
          
          MedicoSeeder::class,
          EspecialidadMedicoSeeder::class,
          PacientesSeeder::class,
          CitasSeeder::class,
        ]);


    }
}
