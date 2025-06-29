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

        // Crear permisos (evitar duplicados)
        $editArticles = Permission::firstOrCreate(['name' => 'edit articles']);
        $deleteArticles = Permission::firstOrCreate(['name' => 'delete articles']);
        $publishArticles = Permission::firstOrCreate(['name' => 'publish articles']);
        $crearPersonas = Permission::firstOrCreate(['name' => 'crear personas']);
        $crearNacionalidad = Permission::firstOrCreate(['name' => 'crear nacionalidad']);
        $crearUsuario = Permission::firstOrCreate(['name' => 'crear usuario']);

        // Crear roles y asignar permisos (evitar duplicados)
        $roleAdmin = Role::firstOrCreate(['name' => 'root']);
        $roleAdmin->givePermissionTo([$editArticles, $deleteArticles, $publishArticles, $crearPersonas, $crearNacionalidad, $crearUsuario]);

        $roleEditor = Role::firstOrCreate(['name' => 'editor']);
        $roleEditor->givePermissionTo([$editArticles, $publishArticles, $crearNacionalidad]);

        $roleAdminPersonas = Role::firstOrCreate(['name' => 'admin personas']);
        $roleAdminPersonas->givePermissionTo([$crearPersonas]);        
    }
}
