<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use App\Models\Medico;
use App\Models\Recetario;
use Illuminate\Support\Facades\Auth;
use Exception;

class PerfilMedico extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Mi Perfil';
    protected static ?string $title = 'Mi Perfil Médico';
    protected static ?string $navigationGroup = 'Mi Cuenta';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.perfil-medico';

    public function getTitle(): string
    {
        $user = Auth::user();
        if ($user && $user->hasRole('root') && !$user->medico) {
            return 'Perfil Médico (Acceso Root)';
        }
        return 'Mi Perfil Médico';
    }

    public ?array $data = [];
    public ?array $recetarioData = [];

    public function mount(): void
    {
        $this->checkMedicoAccess();
        $this->loadMedicoData();
        $this->loadRecetarioData();
        
        // Inicializar los formularios con los datos cargados
        $this->form->fill($this->data);
        $this->getRecetarioForm()->fill($this->recetarioData);
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema($this->getFormSchema())
                    ->statePath('data')
            ),
            'recetarioForm' => $this->makeForm()
                ->schema($this->getRecetarioFormSchema())
                ->statePath('recetarioData')
        ];
    }

    protected function checkMedicoAccess(): void
    {
        $user = Auth::user();
        if (!$user || (!$user->hasRole('medico') && !$user->hasRole('root'))) {
            abort(403, 'Solo los usuarios con rol de médico o root pueden acceder a esta página.');
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
            // Si es root o solo tiene rol de médico pero no registro, usar datos de persona o usuario
            $persona = $user->persona;
            
            if ($persona) {
                $this->data = [
                    'nombre_completo' => trim("{$persona->primer_nombre} {$persona->segundo_nombre} {$persona->primer_apellido} {$persona->segundo_apellido}"),
                    'dni' => $persona->dni,
                    'telefono' => $persona->telefono,
                    'email' => $persona->email,
                    'numero_colegiacion' => 'No asignado',
                    'horario_entrada' => 'No definido',
                    'horario_salida' => 'No definido',
                ];
            } else {
                // Para usuario root sin persona asociada
                $this->data = [
                    'nombre_completo' => $user->name ?? 'Usuario Root',
                    'dni' => 'No definido',
                    'telefono' => 'No definido',
                    'email' => $user->email,
                    'numero_colegiacion' => 'No asignado',
                    'horario_entrada' => 'No definido',
                    'horario_salida' => 'No definido',
                ];
            }
        }
    }

    protected function loadRecetarioData(): void
    {
        $user = Auth::user();
        
        // Si tiene registro de médico, buscar recetarios
        if ($user->medico) {
            $medico = $user->medico;
            $recetario = $medico->recetarios()->latest()->first();

            if ($recetario) {
                // Debug el logo que viene de la BD
                \Illuminate\Support\Facades\Log::debug('Logo de BD: ' . print_r($recetario->logo, true));
                
                $this->recetarioData = [
                    'tiene_recetario' => $recetario->tiene_recetario ?? true,
                    'centro_id' => $recetario->centro_id ?? session('current_centro_id') ?? $user->centro_id,
                    'logo' => is_array($recetario->logo) ? ($recetario->logo[0] ?? null) : $recetario->logo,
                    'encabezado_texto' => $recetario->encabezado_texto ?? 'RECETA MÉDICA',
                    'pie_pagina' => $recetario->pie_pagina ?? 'Consulte a su médico antes de usar cualquier medicamento',
                    'color_primario' => $recetario->color_primario ?? '#2563eb',
                    'color_secundario' => $recetario->color_secundario ?? '#64748b',
                    'fuente_familia' => $recetario->fuente_familia ?? 'Arial',
                    'fuente_tamano' => $recetario->fuente_tamano ?? 12,
                    'mostrar_logo' => $recetario->mostrar_logo ?? true,
                    'mostrar_especialidades' => $recetario->mostrar_especialidades ?? true,
                    'mostrar_telefono' => $recetario->mostrar_telefono ?? true,
                    'mostrar_direccion' => $recetario->mostrar_direccion ?? true,
                    'texto_adicional' => $recetario->texto_adicional ?? '',
                    'formato_papel' => $recetario->formato_papel ?? 'half',
                ];
            } else {
                $this->recetarioData = [
                    'tiene_recetario' => false,
                    'centro_id' => session('current_centro_id') ?? $user->centro_id,
                    'logo' => null,
                    'encabezado_texto' => 'RECETA MÉDICA',
                    'pie_pagina' => 'Consulte a su médico antes de usar cualquier medicamento',
                    'color_primario' => '#2563eb',
                    'color_secundario' => '#64748b',
                    'fuente_familia' => 'Arial',
                    'fuente_tamano' => 12,
                    'mostrar_logo' => true,
                    'mostrar_especialidades' => true,
                    'mostrar_telefono' => true,
                    'mostrar_direccion' => true,
                    'texto_adicional' => '',
                    'formato_papel' => 'half',
                ];
            }
        } else {
            // Si es root o solo tiene rol médico pero no registro de médico
            $this->recetarioData = [
                'tiene_recetario' => false,
                'centro_id' => session('current_centro_id') ?? $user->centro_id ?? null,
                'logo' => null,
                'encabezado_texto' => 'RECETA MÉDICA',
                'pie_pagina' => 'Consulte a su médico antes de usar cualquier medicamento',
                'color_primario' => '#2563eb',
                'color_secundario' => '#64748b',
                'fuente_familia' => 'Arial',
                'fuente_tamano' => 12,
                'mostrar_logo' => true,
                'mostrar_especialidades' => true,
                'mostrar_telefono' => true,
                'mostrar_direccion' => true,
                'texto_adicional' => '',
                'formato_papel' => 'half',
            ];
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }

    protected function getFormSchema(): array
    {
        return [
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
        ];
    }

    protected function getRecetarioFormSchema(): array
    {
        return [
            Section::make('Configuración del Recetario')
                ->description('Active y configure la apariencia de su recetario médico')
                ->schema([
                    Toggle::make('tiene_recetario')
                        ->label('Activar Recetario')
                        ->helperText('Active esta opción para habilitar su recetario personalizado')
                        ->live()
                        ->columnSpanFull(),
                ]),
                
            Section::make('Diseño y Personalización')
                ->description('Configure la apariencia visual de su recetario')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            FileUpload::make('logo')
                                ->label('Logo de la Clínica/Consultorio')
                                ->disk('public')
                                ->directory('recetarios/logos')
                                ->image()
                                ->maxSize(2048)
                                ->helperText('Imagen que aparecerá en el encabezado')
                                ->disabled(fn() => !auth()->user()->can('uploadLogo', self::class))
                                ->multiple(false) // Explícitamente lo configuramos como no múltiple
                                ->live(),
                                
                            Toggle::make('mostrar_logo')
                                ->label('Mostrar Logo')
                                ->helperText('Mostrar u ocultar el logo en el recetario')
                                ->live(),
                        ]),
                        
                    
                ])
                ->visible(fn (callable $get) => $get('tiene_recetario'))
                ->columns(1),
                
            Section::make('Colores y Tipografía')
                ->description('Personalice los colores y fuentes del recetario')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            ColorPicker::make('color_primario')
                                ->label('Color Primario')
                                ->helperText('Color principal del encabezado')
                                ->live(),
                                
                            ColorPicker::make('color_secundario')
                                ->label('Color Secundario')
                                ->helperText('Color de texto secundario')
                                ->live(),
                        ]),
                        
                    Grid::make(2)
                        ->schema([
                            Select::make('fuente_familia')
                                ->label('Familia de Fuente')
                                ->options([
                                    'Arial' => 'Arial',
                                    'Times New Roman' => 'Times New Roman',
                                    'Helvetica' => 'Helvetica',
                                    'Georgia' => 'Georgia',
                                    'Verdana' => 'Verdana',
                                ])
                                ->helperText('Fuente principal del texto')
                                ->live(),
                                
                            Select::make('fuente_tamano')
                                ->label('Tamaño de Fuente')
                                ->options([
                                    10 => '10px',
                                    11 => '11px',
                                    12 => '12px',
                                    14 => '14px',
                                    16 => '16px',
                                    18 => '18px',
                                ])
                                ->helperText('Tamaño base del texto')
                                ->live(),
                        ]),
                ])
                ->visible(fn (callable $get) => $get('tiene_recetario'))
                ->columns(1),
                
            Section::make('Información a Mostrar')
                ->description('Seleccione qué información incluir en el recetario')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('mostrar_especialidades')
                                ->label('Mostrar Especialidades')
                                ->helperText('Incluir especialidades médicas')
                                ->live(),
                                
                            Toggle::make('mostrar_telefono')
                                ->label('Mostrar Teléfono')
                                ->helperText('Incluir número de teléfono')
                                ->live(),
                                
                            Toggle::make('mostrar_direccion')
                                ->label('Mostrar Dirección')
                                ->helperText('Incluir dirección del consultorio')
                                ->live(),
                                
                            Select::make('formato_papel')
                                ->label('Formato de Papel')
                                ->options([
                                    'half' => 'Media Página',
                                    'full' => 'Página Completa',
                                ])
                                ->helperText('Tamaño del recetario')
                                ->live(),
                        ]),
                ])
                ->visible(fn (callable $get) => $get('tiene_recetario'))
                ->columns(2),
                
            Section::make('Vista Previa del Recetario')
                ->description('Visualización en tiempo real de su recetario')
                ->schema([
                    Placeholder::make('preview')
                        ->label('')
                        ->dehydrated(false)
                        ->content(function (callable $get) {
                            // Verificar permisos para ver la vista previa
                            if (!auth()->user()->can('viewPreview', self::class)) {
                                return new \Illuminate\Support\HtmlString(
                                    '<div style="text-align: center; padding: 40px; color: #666;">
                                        <p>No tiene permisos para ver la vista previa del recetario.</p>
                                    </div>'
                                );
                            }
                            
                            $config = $get();
                            // Debug: vamos a ver qué datos llegan
                            // dd($config); // Descomenta para debug
                            return new \Illuminate\Support\HtmlString(
                                view('components.recetario-preview-demo', compact('config'))->render()
                            );
                        }),
                ])
                ->visible(fn (callable $get) => $get('tiene_recetario') && auth()->user()->can('viewPreview', self::class))
                ->collapsible()
                ->collapsed(false),
                
            Section::make('Guardar Configuración')
                ->schema([
                    Placeholder::make('save_button')
                        ->label('')
                        ->content(new \Illuminate\Support\HtmlString('
                            <div class="flex justify-center">
                                <button 
                                    type="button" 
                                    wire:click="saveRecetario"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Guardar Configuración del Recetario
                                </button>
                            </div>
                        ')),
                ])
                ->visible(fn (callable $get) => $get('tiene_recetario')),
        ];
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

    public function save()
    {
        Notification::make()
            ->title('Información')
            ->body('La información personal del médico es de solo lectura.')
            ->info()
            ->send();
    }

    public function saveRecetario()
    {
        try {
            $user = auth()->user();
            
            // Validar permisos usando la policy
            if (!$user->can('updateRecetario', self::class)) {
                $response = \Illuminate\Support\Facades\Gate::inspect('updateRecetario', self::class);
                Notification::make()
                    ->title('Acceso Denegado')
                    ->body($response->message())
                    ->warning()
                    ->send();
                return;
            }
            
            $recetarioData = $this->recetarioData;
            
            // Debug - Ver qué estamos recibiendo
            \Illuminate\Support\Facades\Log::debug('Logo original: ' . print_r($recetarioData['logo'] ?? 'null', true));
            
            // Procesar el logo de forma más segura
            if (!isset($recetarioData['logo'])) {
                $recetarioData['logo'] = null;
            } elseif (is_array($recetarioData['logo'])) {
                \Illuminate\Support\Facades\Log::debug('Logo es array');
                $recetarioData['logo'] = reset($recetarioData['logo']);
                \Illuminate\Support\Facades\Log::debug('Logo transformado: ' . print_r($recetarioData['logo'], true));
            } elseif ($recetarioData['logo'] === '') {
                $recetarioData['logo'] = null;
            }
            
            $medico = $user->medico;

            // Buscar o crear recetario
            $recetario = Recetario::firstOrNew(['medico_id' => $medico->id]);
            
            // Manejar el logo de forma separada
            $logo = null;
            if (isset($recetarioData['logo'])) {
                $logo = $recetarioData['logo'];
                unset($recetarioData['logo']);
            }
            
            // Actualizar datos
            $recetario->fill($recetarioData);
            
            // Establecer el logo manualmente
            if ($logo !== null) {
                \Illuminate\Support\Facades\Log::debug('Estableciendo logo en: ' . print_r($logo, true));
                $recetario->setAttribute('logo', $logo);
            }
            
            $recetario->save();

            Notification::make()
                ->title('Configuración Guardada')
                ->body('La configuración de su recetario se ha guardado correctamente.')
                ->success()
                ->send();

            // Recargar datos
            $this->loadRecetarioData();

        } catch (Exception $e) {
            Notification::make()
                ->title('Error al Guardar')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getRecetarioForm()
    {
        return $this->makeForm()
            ->schema($this->getRecetarioFormSchema())
            ->statePath('recetarioData')
            ->model(Recetario::class);
    }

    public function actualizarRecetario(bool $estado): void
    {
        $user = Auth::user();

        // Verificar que el usuario tenga registro de médico o sea root
        if (!$user->medico && !$user->hasRole('root')) {
            Notification::make()
                ->title('Error')
                ->body('Necesita tener un registro de médico para activar el recetario. Contacte al administrador.')
                ->danger()
                ->send();
            
            // Revertir el estado del toggle
            $this->recetarioData['tiene_recetario'] = false;
            return;
        }

        // Si es root sin registro de médico, mostrar advertencia pero permitir continuar
        if ($user->hasRole('root') && !$user->medico) {
            Notification::make()
                ->title('Advertencia')
                ->body('Usuario root accediendo sin registro de médico asociado.')
                ->warning()
                ->send();
            
            // Para root, solo cambiar el estado en memoria
            $this->recetarioData['tiene_recetario'] = $estado;
            return;
        }

        $medico = $user->medico;

        if (!$medico) {
            Notification::make()
                ->title('Error')
                ->body('No se encontró registro de médico asociado.')
                ->danger()
                ->send();
            
            // Revertir el estado del toggle
            $this->recetarioData['tiene_recetario'] = false;
            return;
        }

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
                'centro_id' => $this->recetarioData['centro_id'] ?? null,
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
        
        if (!$user) {
            return false;
        }

        // Usar la policy para determinar el acceso
        return $user->can('view', self::class);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    // Método para obtener la vista previa actualizada
    public function getPreviewData()
    {
        return $this->recetarioData;
    }
}
