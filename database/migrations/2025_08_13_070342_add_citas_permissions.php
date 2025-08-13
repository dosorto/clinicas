<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear permisos de citas si no existen
        $citasPermissions = [
            'ver citas',
            'crear citas',
            'actualizar citas',
            'borrar citas'
        ];

        foreach ($citasPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Asignar permisos a roles
        $roleRoot = Role::where('name', 'root')->first();
        if ($roleRoot) {
            $roleRoot->givePermissionTo($citasPermissions);
        }

        $roleAdmin = Role::where('name', 'administrador')->first();
        if ($roleAdmin) {
            $roleAdmin->givePermissionTo($citasPermissions);
        }

        $roleMedico = Role::where('name', 'medico')->first();
        if ($roleMedico) {
            // Los médicos solo pueden VER citas (sus propias citas)
            $roleMedico->givePermissionTo(['ver citas']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover permisos de citas
        $citasPermissions = [
            'ver citas',
            'crear citas',
            'actualizar citas',
            'borrar citas'
        ];

        foreach ($citasPermissions as $permission) {
            $perm = Permission::where('name', $permission)->first();
            if ($perm) {
                $perm->delete();
            }
        }
    }
};
