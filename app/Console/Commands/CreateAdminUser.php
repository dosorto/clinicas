<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-admin {email?} {password?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear un usuario administrador';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?: $this->ask('Email del administrador', 'admin@clinica.com');
        $password = $this->argument('password') ?: $this->secret('Contraseña (por defecto: admin123)') ?: 'admin123';
        
        // Verificar si el usuario ya existe
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            if ($this->confirm("El usuario {$email} ya existe. ¿Desea actualizar su contraseña?")) {
                $existingUser->update([
                    'password' => Hash::make($password)
                ]);
                $this->info("Contraseña actualizada para {$email}");
                return 0;
            } else {
                $this->info('Operación cancelada.');
                return 0;
            }
        }

        // Crear nuevo usuario
        $user = User::create([
            'name' => 'Administrador',
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        $this->info("Usuario administrador creado exitosamente:");
        $this->line("Email: {$email}");
        $this->line("Contraseña: {$password}");
        
        return 0;
    }
}
