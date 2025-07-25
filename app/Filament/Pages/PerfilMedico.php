<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use App\Models\Medico;
use App\Models\Recetario;
use Illuminate\Support\Facades\Auth;

class PerfilMedico extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Mi Perfil';
    protected static ?string $title = 'Mi Perfil Médico';
    protected static ?string $navigationGroup = 'Mi Cuenta';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.perfil-medico';

    public ?array $data = [];
    public ?array $recetarioData = [];

    public function mount(): void
    {
        $this->checkMedicoAccess();
        $this->loadMedicoData();
        $this->loadRecetarioData();
        
        // Inicializar el formulario principal con los datos cargados
        $this->form->fill($this->data);
    }

    protected function checkMedicoAccess(): void
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('medico')) {
            abort(403, 'Solo los usuarios con rol de médico pueden acceder a esta página.');
        }
    }

    protected function loadMedicoData(): void
    {
        $user = Auth::user();
        
        // Si tiene un registro de médico, usar esos datos
        if ($user->medico) {
            $medico = $user->medico;
            $persona = $medico->persona;

            $this->data = [
                'nombre_completo' => trim("{$persona->primer_nombre} {$persona->segundo_nombre} {$persona->primer_apellido} {$persona->segundo_apellido}"),
                'dni' => $persona->dni,
                'telefono' => $persona->telefono,
                'email' => $persona->email,
                'numero_colegiacion' => $medico->numero_colegiacion,
                'horario_entrada' => $medico->horario_entrada,
                'horario_salida' => $medico->horario_salida,
            ];
        } else {
            // Si solo tiene rol de médico pero no registro, usar datos de persona
            $persona = $user->persona;
            
            $this->data = [
                'nombre_completo' => trim("{$persona->primer_nombre} {$persona->segundo_nombre} {$persona->primer_apellido} {$persona->segundo_apellido}"),
                'dni' => $persona->dni,
                'telefono' => $persona->telefono,
                'email' => $persona->email,
                'numero_colegiacion' => 'No asignado',
                'horario_entrada' => 'No definido',
                'horario_salida' => 'No definido',
            ];
        }
    }

    protected function loadRecetarioData(): void
    {
        $user = Auth::user();
        
        // Si tiene registro de médico, buscar recetarios
        if ($user->medico) {
            $medico = $user->medico;
            $recetario = $medico->recetarios()->latest()->first();

            $this->recetarioData = [
                'tiene_recetario' => $recetario ? true : false,
                'centro_id' => session('current_centro_id') ?? $user->centro_id,
            ];
        } else {
            // Si solo tiene rol pero no registro de médico
            $this->recetarioData = [
                'tiene_recetario' => false,
                'centro_id' => session('current_centro_id') ?? $user->centro_id,
            ];
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información Personal')
                    ->description('Información básica del médico')
                    ->schema([
                        TextInput::make('nombre_completo')
                            ->label('Nombre Completo')
                            ->disabled()
                            ->columnSpan(2),

                        TextInput::make('dni')
                            ->label('DNI')
                            ->disabled()
                            ->columnSpan(1),

                        TextInput::make('telefono')
                            ->label('Teléfono')
                            ->disabled()
                            ->columnSpan(1),

                        TextInput::make('email')
                            ->label('Email')
                            ->disabled()
                            ->columnSpan(2),

                        TextInput::make('numero_colegiacion')
                            ->label('Número de Colegiación')
                            ->disabled()
                            ->columnSpan(1),

                        TextInput::make('horario_entrada')
                            ->label('Horario de Entrada')
                            ->disabled()
                            ->columnSpan(1),

                        TextInput::make('horario_salida')
                            ->label('Horario de Salida')
                            ->disabled()
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function toggleRecetario(): void
    {
        $this->recetarioData['tiene_recetario'] = !$this->recetarioData['tiene_recetario'];
        $this->actualizarRecetario($this->recetarioData['tiene_recetario']);
    }

    public function cambiarRecetario(): void
    {
        // Este método se llama cuando cambia el checkbox
        $this->actualizarRecetario($this->recetarioData['tiene_recetario']);
    }

    public function actualizarRecetario(bool $estado): void
    {
        $user = Auth::user();

        // Verificar que el usuario tenga registro de médico
        if (!$user->medico) {
            Notification::make()
                ->title('Error')
                ->body('Necesita tener un registro de médico para activar el recetario. Contacte al administrador.')
                ->danger()
                ->send();
            
            // Revertir el estado del toggle
            $this->recetarioData['tiene_recetario'] = false;
            return;
        }

        $medico = $user->medico;

        if (!$estado) {
            // Desactivar recetario existente - eliminar registro
            $medico->recetarios()->delete();
            
            Notification::make()
                ->title('Recetario desactivado')
                ->body('Su recetario ha sido desactivado correctamente.')
                ->success()
                ->send();
            return;
        }

        // Buscar recetario existente o crear uno nuevo
        $recetario = $medico->recetarios()->latest()->first();

        if (!$recetario) {
            // Crear nuevo recetario básico
            Recetario::create([
                'medico_id' => $medico->id,
                'consulta_id' => null,
                'centro_id' => $this->recetarioData['centro_id'],
            ]);
            
            Notification::make()
                ->title('Recetario activado')
                ->body('Su recetario ha sido activado correctamente.')
                ->success()
                ->send();
        }
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('medico');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
