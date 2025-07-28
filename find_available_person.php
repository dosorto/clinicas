<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Buscando persona sin médico ni usuario...\n";

// Buscar persona que no tenga relación con médico ni usuario
$personas = \App\Models\Persona::whereDoesntHave('medico')
    ->whereDoesntHave('user')
    ->take(3)
    ->get();

if ($personas->count() > 0) {
    foreach ($personas as $persona) {
        echo "Persona disponible: {$persona->dni} - {$persona->primer_nombre} {$persona->primer_apellido}\n";
    }
    
    // Usar la primera persona
    $persona = $personas->first();
    echo "\nUsando persona: {$persona->dni}\n";
    
    // Crear médico
    try {
        $medico = \App\Models\Medico::create([
            'persona_id' => $persona->id,
            'numero_colegiacion' => 'COL' . rand(10000, 99999),
            'horario_entrada' => '08:00:00',
            'horario_salida' => '17:00:00',
            'centro_id' => 1
        ]);
        
        echo "Médico creado con ID: {$medico->id}\n";
        
        // Crear usuario
        $username = strtolower($persona->primer_nombre . '.' . $persona->primer_apellido);
        $email = $username . '@clinica.com';
        
        $user = \App\Models\User::create([
            'persona_id' => $persona->id,
            'name' => $username,
            'email' => $email,
            'password' => bcrypt('password123'),
            'centro_id' => 1
        ]);
        
        echo "Usuario creado con ID: {$user->id}\n";
        echo "Total usuarios ahora: " . \App\Models\User::count() . "\n";
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    
} else {
    echo "No hay personas disponibles sin médico ni usuario\n";
    
    // Mostrar estadísticas
    $totalPersonas = \App\Models\Persona::count();
    $personasConMedico = \App\Models\Persona::whereHas('medico')->count();
    $personasConUsuario = \App\Models\Persona::whereHas('user')->count();
    
    echo "Total personas: {$totalPersonas}\n";
    echo "Personas con médico: {$personasConMedico}\n";
    echo "Personas con usuario: {$personasConUsuario}\n";
}
