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
            'ver personas', 'ver nacionalidad', 'ver usuario', 'ver pacientes', 'ver medicocentromedico', 'ver enfermedades', 'ver centromedico', 'ver especialidad', 'ver especialidadmedicos', 'ver medicos', 'ver enfermedades_pacientes', 'ver recetas', 'ver consultas', 'ver medicocentromedico', 'ver centromedico',
            // CREAR
            'crear personas', 'crear nacionalidad', 'crear usuario', 'crear pacientes', 'crear medicocentromedico', 'crear centromedico', 'crear enfermedades', 'crear especialidad', 'crear especialidadmedicos', 'crear medicos', 'crear enfermedades_pacientes', 'crear recetas', 'crear consultas',
            // ACTUALIZAR
            'actualizar personas', 'actualizar nacionalidad', 'actualizar usuario', 'actualizar pacientes', 'actualizar medicocentromedico', 'actualizar enfermedades', 'actualizar centromedico', 'actualizar especialidad', 'actualizar especialidadmedicos', 'actualizar medicos','actualizar enfermedades_pacientes', 'actualizar recetas', 'actualizar consultas', 'actualizar medicocentromedico', 'actualizar centromedico', 'actualizar medicocentromedico',
            // BORRAR
            'borrar personas', 'borrar nacionalidad', 'borrar usuario', 'borrar pacientes', 'borrar centromedico', 'borrar medicocentromedico', 'borrar enfermedades', 'borrar especialidad', 'borrar especialidadmedicos', 'borrar medicos', 'borrar enfermedades_pacientes', 'borrar recetas', 'borrar consultas', 'borrar medicocentromedico', 'borrar centromedico',
        ]);

        $roleAdminNacionalidades = Role::create(['name' => 'admin nacionalidades']);
        $roleAdminNacionalidades->givePermissionTo(['crear nacionalidad', 'ver nacionalidad', 'actualizar nacionalidad', 'borrar nacionalidad']);

        $roleAdminPersonas = Role::create(['name' => 'admin personas']);
        $roleAdminPersonas->givePermissionTo(['crear personas', 'ver personas', 'actualizar personas', 'borrar personas']);
        
        $roleAdminPacientes = Role::create(['name' => 'admin pacientes']);
        $roleAdminPacientes->givePermissionTo(['crear pacientes', 'ver pacientes', 'actualizar pacientes', 'borrar pacientes']);

        $roleAdminPacientes = Role::create(['name' => 'admin enfermedades']);
        $roleAdminPacientes->givePermissionTo(['crear enfermedades', 'ver enfermedades', 'actualizar enfermedades', 'borrar enfermedades']);
        
        $roleAdminEnfermedadesPacientes = Role::create(['name' => 'admin enfermedades_pacientes']);
        $roleAdminEnfermedadesPacientes->givePermissionTo(['crear enfermedades_pacientes', 'ver enfermedades_pacientes', 'actualizar enfermedades_pacientes', 'borrar enfermedades_pacientes']);

        $roleAdminRecetas = Role::create(['name' => 'admin recetas']);
        $roleAdminRecetas->givePermissionTo(['crear recetas', 'ver recetas', 'actualizar recetas', 'borrar recetas']);

        $roleAdminConsultas = Role::create(['name' => 'admin consultas']);
        $roleAdminConsultas->givePermissionTo(['crear consultas', 'ver consultas', 'actualizar consultas', 'borrar consultas']);

        $roleAdminConsultas = Role::create(['name' => 'admin medicocentromedico']);
        $roleAdminConsultas->givePermissionTo(['crear medicocentromedico', 'ver medicocentromedico', 'actualizar medicocentromedico', 'borrar medicocentromedico']);

        $roleAdminCentrosMedicos = Role::create(['name' => 'admin centromedico']);
        $roleAdminCentrosMedicos->givePermissionTo(['crear centromedico', 'ver centromedico', 'actualizar centromedico', 'borrar centromedico']);
    }
}
