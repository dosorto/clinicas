<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos organizados

        // VER
        Permission::create(['name' => 'ver personas']);
        Permission::create(['name' => 'ver nacionalidad']);
        Permission::create(['name' => 'ver usuario']);
        Permission::create(['name' => 'ver pacientes']);
        Permission::create(['name' => 'ver medicocentromedico']);
        Permission::create(['name' => 'ver centromedico']);
        Permission::create(['name' => 'ver enfermedades']);
        Permission::create(['name' => 'ver especialidad']);
        Permission::create(['name' => 'ver especialidadmedicos']);
        Permission::create(['name' => 'ver medicos']);
        Permission::create(['name' => 'ver enfermedades_pacientes']);
        Permission::create(['name' => 'ver recetas']);
        Permission::create(['name' => 'ver consultas']);



        // CREAR
        Permission::create(['name' => 'crear personas']);
        Permission::create(['name' => 'crear nacionalidad']);
        Permission::create(['name' => 'crear usuario']);
        Permission::create(['name' => 'crear pacientes']);
        Permission::create(['name' => 'crear medicocentromedico']);
        Permission::create(['name' => 'crear centromedico']);
        Permission::create(['name' => 'crear enfermedades']);
        Permission::create(['name' => 'crear especialidad']);
        Permission::create(['name' => 'crear especialidadmedicos']);
        Permission::create(['name' => 'crear medicos']);
        Permission::create(['name' => 'crear enfermedades_pacientes']);
        Permission::create(['name' => 'crear recetas']);
        Permission::create(['name' => 'crear consultas']);

        // ACTUALIZAR
        Permission::create(['name' => 'actualizar personas']);
        Permission::create(['name' => 'actualizar nacionalidad']);
        Permission::create(['name' => 'actualizar usuario']);
        Permission::create(['name' => 'actualizar pacientes']);
        Permission::create(['name' => 'actualizar medicocentromedico']);
        Permission::create(['name' => 'actualizar centromedico']);
        Permission::create(['name' => 'actualizar enfermedades']);
        Permission::create(['name' => 'actualizar especialidad']);
        Permission::create(['name' => 'actualizar especialidadmedicos']);
        Permission::create(['name' => 'actualizar medicos']);
        Permission::create(['name' => 'actualizar enfermedades_pacientes']);
        Permission::create(['name' => 'actualizar recetas']);
        Permission::create(['name' => 'actualizar consultas']);

        // BORRAR
        Permission::create(['name' => 'borrar personas']);
        Permission::create(['name' => 'borrar nacionalidad']);
        Permission::create(['name' => 'borrar usuario']);
        Permission::create(['name' => 'borrar pacientes']);
        Permission::create(['name' => 'borrar centromedico']);
        Permission::create(['name' => 'borrar medicocentromedico']);
        Permission::create(['name' => 'borrar enfermedades']);
        Permission::create(['name' => 'borrar especialidad']);
        Permission::create(['name' => 'borrar especialidadmedicos']);
        Permission::create(['name' => 'borrar medicos']);
        Permission::create(['name' => 'borrar enfermedades_pacientes']);
        Permission::create(['name' => 'borrar recetas']);
        Permission::create(['name' => 'borrar consultas']);

        // Crear roles y asignar permisos
        $roleAdmin = Role::create(['name' => 'root']);
        $roleAdmin->givePermissionTo([
            // VER
            'ver personas', 'ver nacionalidad', 'ver usuario', 'ver pacientes', 'ver medicocentromedico', 'ver enfermedades', 'ver centromedico', 'ver especialidad', 'ver especialidadmedicos', 'ver medicos', 'ver enfermedades_pacientes', 'ver recetas', 'ver consultas',
            // CREAR
            'crear personas', 'crear nacionalidad', 'crear usuario', 'crear pacientes', 'crear medicocentromedico', 'crear centromedico', 'crear enfermedades', 'crear especialidad', 'crear especialidadmedicos', 'crear medicos', 'crear enfermedades_pacientes', 'crear recetas', 'crear consultas',
            // ACTUALIZAR
            'actualizar personas', 'actualizar nacionalidad', 'actualizar usuario', 'actualizar pacientes', 'actualizar medicocentromedico', 'actualizar centromedico', 'actualizar enfermedades', 'actualizar especialidad', 'actualizar especialidadmedicos', 'actualizar medicos', 'actualizar enfermedades_pacientes', 'actualizar recetas', 'actualizar consultas',
            // BORRAR
            'borrar personas', 'borrar nacionalidad', 'borrar usuario', 'borrar pacientes', 'borrar centromedico', 'borrar medicocentromedico', 'borrar enfermedades', 'borrar especialidad', 'borrar especialidadmedicos', 'borrar medicos', 'borrar enfermedades_pacientes', 'borrar recetas', 'borrar consultas', 'borrar medicocentromedico', 'borrar centromedico',
        ]);

        $roleAdminCentro = Role::create(['name' => 'administrador']);
        $roleAdminCentro->givePermissionTo([
            // VER
            'ver medicos', 'ver pacientes', 'ver usuario', 'ver enfermedades', 'ver especialidad', 'ver recetas', 'ver consultas',
            // CREAR
            'crear medicos', 'crear pacientes', 'crear usuario', 'crear recetas', 'crear consultas',
            // ACTUALIZAR
            'actualizar medicos', 'actualizar pacientes', 'actualizar usuario', 'actualizar recetas', 'actualizar consultas',
            // BORRAR
            'borrar medicos', 'borrar pacientes', 'borrar usuario', 'borrar recetas', 'borrar consultas'
        ]);

       

        $roleAdminMedicos = Role::create(['name' => 'medico']);
        $roleAdminMedicos->givePermissionTo(['crear pacientes', 'ver pacientes', 'actualizar pacientes', 'borrar pacientes',
            'crear consultas', 'ver consultas', 'actualizar consultas', 'borrar consultas',
            'crear recetas', 'ver recetas', 'actualizar recetas', 'borrar recetas',
            
        ]);
    }
}
