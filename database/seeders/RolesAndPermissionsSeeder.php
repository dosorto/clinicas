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
        Permission::create(['name' => 'edit articles']);
        Permission::create(['name' => 'delete articles']);
        Permission::create(['name' => 'publish articles']);
        Permission::create(['name' => 'unpublish articles']);

        // Crear roles y asignar permisos
        $roleAdmin = Role::create(['name' => 'root']);
        $roleAdmin->givePermissionTo(['edit articles', 'delete articles', 'publish articles', 'unpublish articles']);

        $roleEditor = Role::create(['name' => 'editor']);
        $roleEditor->givePermissionTo(['edit articles', 'publish articles']);
    }
}
