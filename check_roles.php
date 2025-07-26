<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Verificando roles y permisos...\n";

try {
    $roles = \Spatie\Permission\Models\Role::all();
    echo "Roles disponibles:\n";
    foreach ($roles as $role) {
        echo "- {$role->name}\n";
    }
    
    echo "\nVerificando si existe el rol 'medico':\n";
    $medicoRole = \Spatie\Permission\Models\Role::where('name', 'medico')->first();
    if ($medicoRole) {
        echo "✅ Rol 'medico' existe con ID: {$medicoRole->id}\n";
    } else {
        echo "❌ Rol 'medico' NO existe\n";
    }
    
    echo "\nTotal usuarios: " . \App\Models\User::count() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
