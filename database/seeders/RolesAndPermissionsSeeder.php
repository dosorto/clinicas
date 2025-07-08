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

        // Crear permisos

        Permission::create(['name' => 'crear personas']);
        Permission::create(['name' => 'borrar personas']);
        Permission::create(['name' => 'ver personas']);
        Permission::create(['name' => 'crear nacionalidad']);
        Permission::create(['name' => 'borrar nacionalidad']);
        Permission::create(['name' => 'crear usuario']);
        Permission::create(['name' => 'borrar usuario']);
        Permission::create(['name' => 'crear pacientes']);
        Permission::create(['name' => 'borrar pacientes']);
        Permission::create(['name' => 'Crear CentroMedico']);
        Permission::create(['name' => 'crear MedicoCentroMedico']);

        // Crear roles y asignar permisos
        $roleAdmin = Role::create(['name' => 'root']);
        $roleAdmin->givePermissionTo(['crear personas', 'crear nacionalidad', 'crear usuario', 'borrar personas', 'borrar nacionalidad', 'borrar usuario','crear pacientes', 'borrar pacientes', 'Crear CentroMedico', 'crear MedicoCentroMedico']);

        $roleAdminNacionalidades = Role::create(['name' => 'admin nacionalidades']);
        $roleAdminNacionalidades->givePermissionTo(['crear nacionalidad']);

        $roleAdminPersonas = Role::create(['name' => 'admin personas']);
        $roleAdminPersonas->givePermissionTo(['crear personas', 'ver personas']);
        
        $roleAdminPacientes = Role::create(['name' => 'admin pacientes']);
        $roleAdminPacientes->givePermissionTo(['crear pacientes']);
    }
}
