<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Simulando creación completa de médico con usuario...\n";

// Datos de prueba (simulando lo que envía el formulario)
$data = [
    'dni' => '12345678',
    'primer_nombre' => 'Carlos',
    'segundo_nombre' => 'Eduardo',
    'primer_apellido' => 'Rodriguez',
    'segundo_apellido' => 'Lopez',
    'telefono' => '555-0123',
    'direccion' => 'Calle Falsa 123',
    'sexo' => 'M',
    'fecha_nacimiento' => '1980-05-15',
    'nacionalidad_id' => 1,
    'numero_colegiacion' => 'COL12345',
    'horario_entrada' => '08:00:00',
    'horario_salida' => '17:00:00',
    'especialidades' => [1], // ID de especialidad
    'crear_usuario' => true,
    'username' => 'carlos.rodriguez',
    'user_email' => 'carlos.rodriguez@clinica.com',
    'user_password' => 'password123',
    'user_role' => 'medico',
    'user_active' => true,
    'send_welcome_email' => false
];

echo "Usuarios antes: " . \App\Models\User::count() . "\n";

try {
    \DB::beginTransaction();
    
    echo "1. Creando/actualizando persona...\n";
    $persona = \App\Models\Persona::updateOrCreate(
        ['dni' => $data['dni']],
        [
            'primer_nombre' => $data['primer_nombre'],
            'segundo_nombre' => $data['segundo_nombre'],
            'primer_apellido' => $data['primer_apellido'],
            'segundo_apellido' => $data['segundo_apellido'],
            'telefono' => $data['telefono'],
            'direccion' => $data['direccion'],
            'sexo' => $data['sexo'],
            'fecha_nacimiento' => $data['fecha_nacimiento'],
            'nacionalidad_id' => $data['nacionalidad_id'],
        ]
    );
    echo "   ✅ Persona ID: {$persona->id}\n";
    
    echo "2. Creando médico...\n";
    $medico = \App\Models\Medico::create([
        'persona_id' => $persona->id,
        'numero_colegiacion' => $data['numero_colegiacion'],
        'horario_entrada' => $data['horario_entrada'],
        'horario_salida' => $data['horario_salida'],
        'centro_id' => 1,
    ]);
    echo "   ✅ Médico ID: {$medico->id}\n";
    
    echo "3. Sincronizando especialidades...\n";
    if (isset($data['especialidades'])) {
        $medico->especialidades()->sync($data['especialidades']);
        echo "   ✅ Especialidades sincronizadas\n";
    }
    
    echo "4. Creando usuario...\n";
    if ($data['crear_usuario']) {
        // Verificar duplicados
        if (\App\Models\User::where('name', $data['username'])->exists()) {
            throw new Exception("Username '{$data['username']}' ya existe");
        }
        if (\App\Models\User::where('email', $data['user_email'])->exists()) {
            throw new Exception("Email '{$data['user_email']}' ya existe");
        }
        
        $user = \App\Models\User::create([
            'name' => $data['username'],
            'email' => $data['user_email'],
            'password' => bcrypt($data['user_password']),
            'persona_id' => $persona->id,
            'centro_id' => 1,
            'email_verified_at' => $data['user_active'] ? now() : null,
        ]);
        echo "   ✅ Usuario ID: {$user->id}\n";
        
        echo "5. Asignando rol...\n";
        $user->assignRole($data['user_role']);
        echo "   ✅ Rol '{$data['user_role']}' asignado\n";
    }
    
    \DB::commit();
    echo "\n✅ TRANSACCIÓN CONFIRMADA\n";
    
} catch (Exception $e) {
    \DB::rollBack();
    echo "\n❌ ERROR - ROLLBACK EJECUTADO\n";
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nUsuarios después: " . \App\Models\User::count() . "\n";
echo "Médicos total: " . \App\Models\Medico::count() . "\n";
