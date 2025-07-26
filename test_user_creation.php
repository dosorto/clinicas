<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Creando médico de prueba...\n";

$persona = \App\Models\Persona::first();
if (!$persona) {
    echo "No hay personas en la BD\n";
    exit;
}

echo "Persona encontrada: {$persona->dni}\n";

// Verificar si ya existe un médico para esta persona
$existingMedico = \App\Models\Medico::where('persona_id', $persona->id)->first();
if ($existingMedico) {
    echo "Ya existe un médico para esta persona, usando existente...\n";
    $medico = $existingMedico;
} else {
    $medico = \App\Models\Medico::create([
        'persona_id' => $persona->id,
        'numero_colegiacion' => 'TEST' . rand(100, 999),
        'horario_entrada' => '08:00:00',
        'horario_salida' => '17:00:00',
        'centro_id' => 1
    ]);
    echo "Médico creado con ID: {$medico->id}\n";
}

// Verificar si ya existe un usuario para esta persona
$existingUser = \App\Models\User::where('persona_id', $persona->id)->first();
if ($existingUser) {
    echo "Ya existe un usuario para esta persona: {$existingUser->email}\n";
} else {
    $username = $persona->primer_nombre . '.' . $persona->primer_apellido;
    $email = strtolower($username) . '@clinica.com';
    
    echo "Creando usuario con username: {$username} y email: {$email}\n";
    
    try {
        $user = \App\Models\User::create([
            'persona_id' => $persona->id,
            'name' => $persona->primer_nombre . ' ' . $persona->primer_apellido,
            'email' => $email,
            'password' => bcrypt('password123'),
            'centro_id' => 1
        ]);
        
        echo "Usuario creado con ID: {$user->id}\n";
    } catch (Exception $e) {
        echo "Error al crear usuario: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
    }
}

echo "Total usuarios ahora: " . \App\Models\User::count() . "\n";
