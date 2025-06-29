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
        Permission::create(['name' => 'crear nacionalidad']);
        Permission::create(['name' => 'borrar nacionalidad']);
        Permission::create(['name' => 'crear usuario']);
        Permission::create(['name' => 'borrar usuario']);

        // Crear roles y asignar permisos
        $roleAdmin = Role::create(['name' => 'root']);
        $roleAdmin->givePermissionTo(['crear personas', 'crear nacionalidad', 'crear usuario', 'borrar personas', 'borrar nacionalidad', 'borrar usuario']);

        $roleEditor = Role::create(['name' => 'admin nacionalidades']);
        $roleEditor->givePermissionTo(['crear nacionalidad']);

        $roleAdminPersonas = Role::create(['name' => 'admin personas']);
        $roleAdminPersonas->givePermissionTo(['crear personas']);        
    }
}
