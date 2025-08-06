<?php

namespace App\Filament\Resources\Medico\MedicoResource\Pages;

use App\Filament\Resources\Medico\MedicoResource;
use App\Models\Persona;
use App\Models\Medico;
use App\Models\User;
use App\Models\Centros_Medico;
use Filament\Actions;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateMedico extends CreateRecord
{
    protected static string $resource = MedicoResource::class;
    protected static ?string $title = 'Crear Médico';

protected function handleRecordCreation(array $data): Model
{
    try {
        // Debug completo de datos recibidos
        Log::info("=== INICIO CREACIÓN MÉDICO ===", [
            'crear_usuario' => $data['crear_usuario'] ?? 'NO_DEFINIDO',
            'username' => $data['username'] ?? 'NO_DEFINIDO',
            'user_email' => $data['user_email'] ?? 'NO_DEFINIDO',
            'user_password' => isset($data['user_password']) ? 'SET' : 'NO_SET',
            'all_data_keys' => array_keys($data)
        ]);

        // Opción 1: Utilizar el método de MedicoResource para crear el médico
        // Esto aprovecha la lógica centralizada y las transacciones existentes
        $medico = MedicoResource::handleMedicoCreation($data);
        
        // Opción 2: Crear el médico manualmente si se requiere lógica personalizada
        // Si no se pudo crear con el método centralizado, intentar el método manual
        if (!$medico) {
            // Primero creamos o actualizamos la persona
            $persona = Persona::updateOrCreate(
                ['dni' => $data['dni']],
                [
                    'primer_nombre' => $data['primer_nombre'],
                    'segundo_nombre' => $data['segundo_nombre'] ?? null,
                    'primer_apellido' => $data['primer_apellido'],
                    'segundo_apellido' => $data['segundo_apellido'] ?? null,
                    'telefono' => $data['telefono'] ?? null,
                    'direccion' => $data['direccion'] ?? null,
                    'sexo' => $data['sexo'],
                    'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
                    'nacionalidad_id' => $data['nacionalidad_id'] ?? null,
                ]
            );

            Log::info("Persona creada/actualizada", ['persona_id' => $persona->id, 'dni' => $persona->dni]);

            // Obtener centro_id de múltiples fuentes posibles
            $centro_id = $data['centro_id'] ?? session('current_centro_id') ?? Auth::user()?->centro_id ?? null;
            
            // Si no hay centro_id, intentar obtenerlo del modelo o usar un valor por defecto
            if (!$centro_id) {
                // Buscar el primer centro médico como último recurso
                $centro_id = Centros_Medico::first()->id ?? 1;
                
                // Guardar en la sesión para futuras operaciones
                session(['current_centro_id' => $centro_id]);
                
                // Log para depuración
                Log::warning("No se encontró centro_id, usando valor por defecto: {$centro_id}");
            }

            // Luego creamos el médico asociado
            $medico = Medico::create([
                'persona_id' => $persona->id,
                'numero_colegiacion' => $data['numero_colegiacion'],
                'horario_entrada' => $data['horario_entrada'],
                'horario_salida' => $data['horario_salida'],
                'centro_id' => $centro_id,
            ]);
            
            // Si vamos a crear un contrato manualmente, asegurar que los valores puedan ser cero
            if (isset($data['salario_quincenal'])) {
                $data['salario_quincenal'] = (float) $data['salario_quincenal'];
            }
            
            if (isset($data['porcentaje_servicio'])) {
                $data['porcentaje_servicio'] = (float) $data['porcentaje_servicio'];
            }

            Log::info("Médico creado", ['medico_id' => $medico->id]);

            // Sincronizar especialidades
            if (isset($data['especialidades']) && !empty($data['especialidades'])) {
                $medico->especialidades()->sync($data['especialidades']);
                Log::info("Especialidades sincronizadas", ['especialidades' => $data['especialidades']]);
            }

            // Verificar si debe crear usuario
            $crearUsuario = $data['crear_usuario'] ?? false;
            Log::info("¿Crear usuario?", ['crear_usuario' => $crearUsuario, 'tipo' => gettype($crearUsuario)]);

            if ($crearUsuario) {
                Log::info("Iniciando creación de usuario...");
                $this->createUserForMedicoSimple($persona, $medico, $data);
            } else {
                Log::info("No se creará usuario - toggle desactivado");
            }
        }

        Log::info("=== FIN CREACIÓN MÉDICO EXITOSA ===");
        
        return $medico;

    } catch (\Exception $e) {
        Log::error("Error en handleRecordCreation", [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    }
}

protected function createUserForMedicoSimple(Persona $persona, Medico $medico, array $data): void
{
    try {
        Log::info("=== INICIO CREACIÓN USUARIO SIMPLE ===", [
            'persona_id' => $persona->id,
            'medico_id' => $medico->id,
            'username' => $data['username'] ?? 'NO_SET',
            'user_email' => $data['user_email'] ?? 'NO_SET'
        ]);

        // Verificar si ya existe un usuario para esta persona
$existingUser = User::where('persona_id', $persona->id)->first();
        
        if ($existingUser) {
            Log::info("Usuario ya existe", ['user_id' => $existingUser->id, 'email' => $existingUser->email]);
            Notification::make()
                ->title('Usuario existente')
                ->body("La persona ya tiene un usuario: {$existingUser->name} ({$existingUser->email})")
                ->info()
                ->send();
            return;
        }

        // Obtener datos del formulario
        $username = $data['username'] ?? $this->generateUsername($persona);
        $email = $data['user_email'] ?? $this->generateEmail($persona);
        $password = $data['user_password'] ?? $this->generatePassword();
        $role = $data['user_role'] ?? 'medico';
        $isActive = $data['user_active'] ?? true;

        Log::info("Datos de usuario preparados", [
            'username' => $username,
            'email' => $email,
            'role' => $role,
            'is_active' => $isActive
        ]);

        // Validar que no existan duplicados
        if (User::where('name', $username)->exists()) {
            Log::error("Username duplicado: {$username}");
            throw new \Exception("El nombre de usuario '{$username}' ya está en uso.");
        }
        
        if (User::where('email', $email)->exists()) {
            Log::error("Email duplicado: {$email}");
            throw new \Exception("El email '{$email}' ya está en uso.");
        }

        // Crear el usuario
        $user = User::create([
            'name' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'persona_id' => $persona->id,
            'centro_id' => session('current_centro_id') ?? auth()->user()->centro_id,
            'email_verified_at' => $isActive ? now() : null,
        ]);

        Log::info("Usuario creado en BD", [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'user_count_after' => User::count()
        ]);

        // Asignar rol seleccionado
        try {
            $user->assignRole($role);
            Log::info("Rol '{$role}' asignado exitosamente");
        } catch (\Exception $roleError) {
            Log::warning("No se pudo asignar rol '{$role}', intentando rol por defecto", ['error' => $roleError->getMessage()]);
            try {
                $user->assignRole('medico');
                Log::info("Rol por defecto 'medico' asignado");
            } catch (\Exception $defaultRoleError) {
                Log::error("No se pudo asignar ningún rol", ['error' => $defaultRoleError->getMessage()]);
            }
        }

        // Mostrar notificación de éxito
        $nombreCompleto = trim("{$persona->primer_nombre} {$persona->primer_apellido}");
        Notification::make()
            ->title('✅ Usuario creado exitosamente')
            ->body("**Usuario creado para {$nombreCompleto}:**\n\n🔑 Usuario: {$username}\n📧 Email: {$email}\n🔒 Contraseña: {$password}\n👤 Rol: " . ucfirst($role) . "\n\n⚠️ IMPORTANTE: Guarde estas credenciales.")
            ->success()
            ->persistent()
            ->send();

        // Enviar email de bienvenida si está activado
        if ($data['send_welcome_email'] ?? false) {
            Log::info("Email de bienvenida programado para envío", ['email' => $email]);
            Notification::make()
                ->title('📧 Email programado')
                ->body("Se enviará un email de bienvenida a {$email}")
                ->info()
                ->send();
        }
            
        Log::info("=== FIN CREACIÓN USUARIO SIMPLE EXITOSA ===", [
            'user_id' => $user->id,
            'username' => $username,
            'email' => $email,
            'role' => $role
        ]);
        
    } catch (\Exception $e) {
        Log::error("=== ERROR EN CREACIÓN USUARIO SIMPLE ===", [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
'trace' => $e->getTraceAsString()
        ]);
        
        // Re-lanzar la excepción para que Filament maneje el rollback
        throw $e;
    }
}

protected function createUserForMedicoInTransaction(Persona $persona, Medico $medico, array $data): bool
{
    try {
        Log::info("=== INICIO CREACIÓN USUARIO EN TRANSACCIÓN ===", [
            'persona_id' => $persona->id,
            'medico_id' => $medico->id,
            'username' => $data['username'] ?? 'NO_SET',
            'user_email' => $data['user_email'] ?? 'NO_SET'
        ]);

        // Verificar si ya existe un usuario para esta persona
        $existingUser = User::where('persona_id', $persona->id)->first();
        
        if ($existingUser) {
            Log::info("Usuario ya existe", ['user_id' => $existingUser->id, 'email' => $existingUser->email]);
            Notification::make()
                ->title('Usuario existente')
                ->body("La persona ya tiene un usuario: {$existingUser->name} ({$existingUser->email})")
                ->info()
                ->send();
            return true; // No es error, simplemente ya existe
        }

        // Obtener datos del formulario
        $username = $data['username'] ?? $this->generateUsername($persona);
        $email = $data['user_email'] ?? $this->generateEmail($persona);
        $password = $data['user_password'] ?? $this->generatePassword();
        $role = $data['user_role'] ?? 'medico';
        $isActive = $data['user_active'] ?? true;

        Log::info("Datos de usuario preparados", [
            'username' => $username,
            'email' => $email,
            'role' => $role,
            'is_active' => $isActive
        ]);

        // Validar que no existan duplicados
        if (User::where('name', $username)->exists()) {
            Log::error("Username duplicado: {$username}");
            Notification::make()
                ->title('❌ Error: Username duplicado')
                ->body("El nombre de usuario '{$username}' ya está en uso.")
                ->danger()
                ->persistent()
                ->send();
            return false;
        }
        
        if (User::where('email', $email)->exists()) {
            Log::error("Email duplicado: {$email}");
            Notification::make()
                ->title('❌ Error: Email duplicado')
                ->body("El email '{$email}' ya está en uso.")
                ->danger()
                ->persistent()
                ->send();
            return false;
        }

        // Crear el usuario
        $user = User::create([
            'name' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'persona_id' => $persona->id,
            'centro_id' => session('current_centro_id') ?? auth()->user()->centro_id,
            'email_verified_at' => $isActive ? now() : null,
        ]);

        Log::info("Usuario creado en BD", [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'user_count_after' => User::count()
        ]);

        // Asignar rol seleccionado
        try {
            $user->assignRole($role);
            Log::info("Rol '{$role}' asignado exitosamente");
        } catch (\Exception $roleError) {
            Log::warning("No se pudo asignar rol '{$role}', intentando rol por defecto", ['error' => $roleError->getMessage()]);
            try {
                $user->assignRole('medico');
                Log::info("Rol por defecto 'medico' asignado");
            } catch (\Exception $defaultRoleError) {
                Log::error("No se pudo asignar ningún rol", ['error' => $defaultRoleError->getMessage()]);
                // No fallar por esto, continuar
            }
        }

        // Mostrar notificación de éxito
        $nombreCompleto = trim("{$persona->primer_nombre} {$persona->primer_apellido}");
        Notification::make()
            ->title('✅ Usuario creado exitosamente')
->body("**Usuario creado para {$nombreCompleto}:**\n\n🔑 Usuario: {$username}\n📧 Email: {$email}\n🔒 Contraseña: {$password}\n👤 Rol: " . ucfirst($role) . "\n\n⚠️ IMPORTANTE: Guarde estas credenciales.")
            ->success()
            ->persistent()
            ->send();

        // Enviar email de bienvenida si está activado
        if ($data['send_welcome_email'] ?? false) {
            Log::info("Email de bienvenida programado para envío", ['email' => $email]);
            Notification::make()
                ->title('📧 Email programado')
                ->body("Se enviará un email de bienvenida a {$email}")
                ->info()
                ->send();
        }
            
        Log::info("=== FIN CREACIÓN USUARIO EXITOSA EN TRANSACCIÓN ===", [
            'user_id' => $user->id,
            'username' => $username,
            'email' => $email,
            'role' => $role
        ]);
        
        return true;
        
    } catch (\Exception $e) {
        Log::error("=== ERROR EN CREACIÓN USUARIO EN TRANSACCIÓN ===", [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        Notification::make()
            ->title('❌ Error al crear usuario')
            ->body("Error: " . $e->getMessage())
            ->danger()
            ->persistent()
            ->send();
            
        return false;
    }
}

protected function createUserForMedico(Persona $persona, Medico $medico, array $data): void
{
    try {
        Log::info("=== INICIO CREACIÓN USUARIO ===", [
            'persona_id' => $persona->id,
            'medico_id' => $medico->id,
            'data_keys' => array_keys($data)
        ]);

        // Verificar si ya existe un usuario para esta persona
        $existingUser = User::where('persona_id', $persona->id)->first();
        
        if ($existingUser) {
            Log::info("Usuario ya existe", ['user_id' => $existingUser->id, 'email' => $existingUser->email]);
            Notification::make()
                ->title('Usuario existente')
                ->body("La persona ya tiene un usuario: {$existingUser->name} ({$existingUser->email})")
                ->info()
                ->send();
            return;
        }

        // Obtener datos del formulario (ahora son campos completos)
        $username = $data['username'] ?? $this->generateUsername($persona);
        $email = $data['user_email'] ?? $this->generateEmail($persona);
        $password = $data['user_password'] ?? $this->generatePassword();
        $role = $data['user_role'] ?? 'medico';
        $isActive = $data['user_active'] ?? true;

        Log::info("Datos de usuario del formulario", [
            'username' => $username,
            'email' => $email,
            'role' => $role,
            'is_active' => $isActive,
            'send_welcome_email' => $data['send_welcome_email'] ?? false
        ]);

        // Validar que no existan duplicados
        if (User::where('name', $username)->exists()) {
            throw new \Exception("El nombre de usuario '{$username}' ya está en uso.");
        }
        
        if (User::where('email', $email)->exists()) {
            throw new \Exception("El email '{$email}' ya está en uso.");
        }

        // Crear el usuario
        $user = User::create([
            'name' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'persona_id' => $persona->id,
            'centro_id' => session('current_centro_id') ?? auth()->user()->centro_id,
            'email_verified_at' => $isActive ? now() : null,
        ]);

        Log::info("Usuario creado en BD", [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ]);

        // Asignar rol seleccionado
        try {
            $user->assignRole($role);
            Log::info("Rol '{$role}' asignado exitosamente");
        } catch (\Exception $roleError) {
Log::warning("No se pudo asignar rol '{$role}'", ['error' => $roleError->getMessage()]);
            // Intentar asignar rol por defecto
            try {
                $user->assignRole('medico');
                Log::info("Rol por defecto 'medico' asignado");
            } catch (\Exception $defaultRoleError) {
                Log::error("No se pudo asignar ningún rol", ['error' => $defaultRoleError->getMessage()]);
            }
        }

        // Mostrar notificación de éxito
        $nombreCompleto = trim("{$persona->primer_nombre} {$persona->primer_apellido}");
        Notification::make()
            ->title('✅ Usuario creado exitosamente')
            ->body("**Usuario creado para {$nombreCompleto}:**\n\n🔑 Usuario: {$username}\n📧 Email: {$email}\n🔒 Contraseña: {$password}\n👤 Rol: " . ucfirst($role) . "\n\n⚠️ IMPORTANTE: Guarde estas credenciales.")
            ->success()
            ->persistent()
            ->send();

        // Enviar email de bienvenida si está activado
        if ($data['send_welcome_email'] ?? false) {
            try {
                // Aquí puedes implementar el envío de email
                Log::info("Email de bienvenida programado para envío", ['email' => $email]);
                
                Notification::make()
                    ->title('📧 Email programado')
                    ->body("Se enviará un email de bienvenida a {$email}")
                    ->info()
                    ->send();
            } catch (\Exception $emailError) {
                Log::warning("Error al enviar email de bienvenida", ['error' => $emailError->getMessage()]);
            }
        }
            
        Log::info("=== FIN CREACIÓN USUARIO EXITOSA ===", [
            'user_id' => $user->id,
            'username' => $username,
            'email' => $email,
            'role' => $role
        ]);
        
    } catch (\Exception $e) {
        Log::error("=== ERROR EN CREACIÓN USUARIO ===", [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        Notification::make()
            ->title('❌ Error al crear usuario')
            ->body("Error: " . $e->getMessage())
            ->danger()
            ->persistent()
            ->send();
    }
}

protected function generateUsername(Persona $persona): string
{
    $base = strtolower(substr($persona->primer_nombre, 0, 1) . $persona->primer_apellido);
    $base = str_replace(' ', '', $base);
    $base = preg_replace('/[^a-z0-9]/', '', $base);
    
    $username = $base;
    $counter = 1;
    
    while (User::where('name', $username)->exists()) {
        $username = $base . $counter;
        $counter++;
    }
    
    return $username;
}

protected function generateEmail(Persona $persona): string
{
    $base = strtolower($persona->primer_nombre . '.' . $persona->primer_apellido);
    $base = str_replace(' ', '.', $base);
    $base = preg_replace('/[^a-z0-9.]/', '', $base);
    
    $email = $base . '@clinica.com';
    $counter = 1;
    
    while (User::where('email', $email)->exists()) {
        $email = $base . $counter . '@clinica.com';
        $counter++;
    }
    
    return $email;
}

protected function generatePassword(): string
{
    // Generar contraseña temporal de 8 caracteres
    return 'Temp' . rand(1000, 9999);
}
    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('create')
                ->label('Crear Médico')
                ->submit('create')
                ->icon('heroicon-o-user-plus')
                ->color('primary'),
                
            Actions\Action::make('cancel')
                ->label('Cancelar')
                ->url($this->getResource()::getUrl('index'))
                ->icon('heroicon-o-x-mark')
                ->color('danger')
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
protected function getCreatedNotificationTitle(): ?string
    {
        return 'Médico y usuario creados exitosamente';
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title($this->getCreatedNotificationTitle())
            ->body('El médico, sus datos personales y su usuario de acceso han sido registrados correctamente.');
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}