<?php


namespace App\Filament\Resources\Medico;

use App\Filament\Resources\Medico\MedicoResource\Pages;
use App\Models\Persona;
use App\Models\Medico;
use App\Models\Nacionalidad;
use App\Models\Especialidad;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Wizard;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Get;
use Closure;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;

class MedicoResource extends Resource
{
    protected static ?string $model = Medico::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationGroup = 'Gestión de Personas';
    protected static ?string $navigationLabel = 'Médicos';
    protected static ?string $modelLabel = 'Médico';
    protected static ?string $pluralModelLabel = 'Médicos';

    public static function form(Form $form): Form
    {
    return $form
    ->schema([
        Forms\Components\Hidden::make('centro_id')
            ->default(fn() => auth()->user()->centro_id),
            
        Wizard::make([
            Wizard\Step::make('Datos Personales')
                ->schema([

                        Forms\Components\TextInput::make('dni')
                            ->label('DNI')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ingrese su DNI')
                            ->disabled(fn ($operation) => $operation === 'edit')
                            ->dehydrated()
                            ->live(debounce: 500) // Esto hace que se actualice cada 500ms después de dejar de escribir
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (strlen($state) >= 8) { // Asumiendo que el DNI tiene al menos 8 caracteres
                                    $existingPersona = Persona::where('dni', $state)->first();
                                    if ($existingPersona) {
                                        $set('primer_nombre', $existingPersona->primer_nombre);
                                        $set('segundo_nombre', $existingPersona->segundo_nombre);
                                        $set('primer_apellido', $existingPersona->primer_apellido);
                                        $set('segundo_apellido', $existingPersona->segundo_apellido);
                                        $set('telefono', $existingPersona->telefono);
                                        $set('direccion', $existingPersona->direccion);
                                        $set('sexo', $existingPersona->sexo);
                                        $set('fecha_nacimiento', $existingPersona->fecha_nacimiento);
                                        $set('nacionalidad_id', $existingPersona->nacionalidad_id);
                                        $set('persona_id', $existingPersona->id);
                                        
                                        Notification::make()
                                            ->title('Persona encontrada')
                                            ->body("Se encontró: {$existingPersona->nombre_completo}")
                                            ->success()
                                            ->send();
                                        } else {
                                            $set('persona_id', null);
                                                            // Opcional: limpiar campos si no se encuentra la persona
                                        if ($get('id') === null) { // Solo en creación
                                            $set('primer_nombre', '');
                                            $set('segundo_nombre', '');
                                            $set('primer_apellido', '');
                                            $set('segundo_apellido', '');
                                            $set('telefono', '');
                                            $set('direccion', '');
                                            $set('sexo', '');
                                            $set('fecha_nacimiento', null);
                                            $set('nacionalidad_id', null);
                                            }
                                        }
                                    }
                                }),
                       /* ->rules([
                            function (Get $get) {
                                return function (string $attribute, $value, Closure $fail) use ($get) {
                                    // Solo validar durante creación
                                    if ($get('id') === null) {
                                        $exists = Persona::where('dni', $value)->exists();
                                        if ($exists) {
                                            $fail('Este DNI ya está registrado por otra persona');
                                        }
                                    }
                                    // Guardar datos en session o en propiedad Livewire si usas Livewire Component
                                    session(['dni' => $value]);
                                };
                            },
                        ]),*/




                    Forms\Components\TextInput::make('primer_nombre')
                        ->label('Primer Nombre')
                        ->required()
                        ->placeholder('Ingrese su primer nombre')
                        ->maxLength(255),
                        
                    Forms\Components\TextInput::make('segundo_nombre')
                        ->label('Segundo Nombre')
                        ->maxLength(255)
                        ->placeholder('Ingrese su segundo nombre')
                        ->nullable(),
                        
                    Forms\Components\TextInput::make('primer_apellido')
                        ->label('Primer Apellido')
                        ->required()
                        ->placeholder('Ingrese su primer apellido')
                        ->maxLength(255),
                        
                    Forms\Components\TextInput::make('segundo_apellido')
                        ->label('Segundo Apellido')
                        ->maxLength(255)
                        ->placeholder('Ingrese su segundo apellido')
                        ->nullable(),
                        
                   
                    Forms\Components\TextInput::make('telefono')
                        ->label('Teléfono')
                        ->maxLength(255)
                        ->placeholder('Ingrese su número de teléfono')
                        ->required(),
                        
                    Forms\Components\Textarea::make('direccion')
                        ->label('Dirección')
                        ->maxLength(255)
                        ->placeholder('Ingrese su dirección')
                        ->required(), // hace obligatorio el campo,
                       // ->columnSpanFull(),
                        
                    Forms\Components\Select::make('sexo')
                        ->label('Sexo')
                        ->placeholder('Seleccione su sexo')
                        ->options([
                            'M' => 'Masculino',
                            'F' => 'Femenino',
                        ])
                        ->required(),

    
                        
                    Forms\Components\DatePicker::make('fecha_nacimiento')
                        ->label('Fecha de Nacimiento')
                        ->native(false)
                        ->placeholder('Seleccione su fecha de nacimiento')
                        ->maxDate(now()) // No permitir fechas futuras
                        ->minDate(now()->subYears(120)) // No permitir fechas demasiado antiguas
                        ->default(now()->subYears(70)) // Valor por defecto (70 años atrás)
                        ->displayFormat('d/m/Y') // Formato de visualización
                        ->required(),

                    Forms\Components\Select::make('nacionalidad_id')
                        ->label('Nacionalidad')
                        ->options(Nacionalidad::pluck('nacionalidad', 'id'))
                        ->searchable()
                        ->placeholder('Seleccione una nacionalidad')
                        ->required(),
                        
                    Forms\Components\FileUpload::make('persona.foto')
                    ->label('Fotografía')
                    ->image()
                    ->maxSize(2048)
                    ->placeholder('Seleccione una fotografía')
                    ->directory('personas/fotos') // Carpeta donde se guardarán las imágenes
                    ->visibility('public') // O 'private' según tus necesidades
                    ->imageEditor(), // Opcional: permite recortar/editar la imagen
                    //->columnSpanFull(),
                     
                ])
                ->columns(2),

                
                
            Wizard\Step::make('Datos Profesionales')
                ->schema([
                    Forms\Components\Hidden::make('centro_id')
                        ->default(fn() => session('current_centro_id')),
                        
                    Forms\Components\TextInput::make('numero_colegiacion')
                        ->label('Número de Colegiación')
                        ->required()
                        ->maxLength(20)
                        ->placeholder('Ingrese su número de colegiación'),
                      // ->unique('medicos', 'numero_colegiacion', ignoreRecord: true),

                       Forms\Components\Grid::make(2)
            ->schema([
                Forms\Components\TimePicker::make('horario_entrada')
                    ->label('Horario de Entrada')
                    ->seconds(false)
                    ->required()
                    ->format('H:i')
                    ->displayFormat('g:i A')
                    ->placeholder('Ej: 8:00 AM')
                    ->suffixIcon('heroicon-o-clock')
                    ->native(false)
                    ->helperText('Horario de inicio de consultas')
                    ->extraAttributes(['class' => 'text-center']),

                Forms\Components\TimePicker::make('horario_salida')
                    ->label('Horario de Salida')
                    ->seconds(false)
                    ->required()
                    ->format('H:i')
                    ->displayFormat('g:i A')
                    ->placeholder('Ej: 5:00 PM')
                    ->suffixIcon('heroicon-o-clock')
                    ->native(false)
                    ->helperText('Horario de fin de consultas')
                    ->extraAttributes(['class' => 'text-center'])
                    ->rules([
                        function (Get $get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                $entrada = $get('horario_entrada');
                                if ($entrada && $value) {
                                    if (strtotime($value) <= strtotime($entrada)) {
                                        $fail('El horario de salida debe ser posterior al horario de entrada');
                                    }
                                    
                                    // Validar que no sea muy temprano o muy tarde
                                    $horaEntrada = (int) date('H', strtotime($entrada));
                                    $horaSalida = (int) date('H', strtotime($value));
                                    
                                    if ($horaEntrada < 6 || $horaSalida > 22) {
                                        $fail('Los horarios deben estar entre las 6:00 AM y 10:00 PM');
                                    }
                                    
                                    // Validar duración mínima de 2 horas
                                    $diferencia = strtotime($value) - strtotime($entrada);
                                    if ($diferencia < 7200) { // 2 horas en segundos
                                        $fail('La jornada debe tener al menos 2 horas de duración');
                                    }
                                }
                            };
                        },
                    ]),
            ]),
                ]) ->columns(2),
                
            Wizard\Step::make('Información Contractual')
                ->description('Información del contrato laboral')
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('salario_quincenal')
                                ->label('Salario Quincenal')
                                ->required()
                                ->numeric()
                                ->prefix('L')
                                ->placeholder('0.00')
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $set('salario_mensual', $state * 2);
                                    }
                                }),

                            Forms\Components\TextInput::make('salario_mensual')
                                ->label('Salario Mensual')
                                ->required()
                                ->numeric()
                                ->prefix('L')
                                ->placeholder('0.00')
                                ->disabled()
                                ->dehydrated(),
                        ]),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('porcentaje_servicio')
                                ->label('Porcentaje por Servicios')
                                ->numeric()
                                ->suffix('%')
                                ->placeholder('0')
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(100)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state === '' || $state === null) {
                                        $set('porcentaje_servicio', 0);
                                    }
                                    // Convertir a número para evitar problemas con strings vacíos
                                    $set('porcentaje_servicio', floatval($state ?? 0));
                                }),

                            Forms\Components\DatePicker::make('fecha_inicio')
                                ->label('Fecha de Inicio')
                                ->required()
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->default(now())
                                ->minDate(now()),
                        ]),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\DatePicker::make('fecha_fin')
                                ->label('Fecha de Finalización')
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->minDate(fn (Get $get) => $get('fecha_inicio'))
                                ->placeholder('Sin fecha de finalización')
                                ->helperText('Dejar vacío si el contrato es indefinido'),

                            Forms\Components\Toggle::make('activo')
                                ->label('Contrato Activo')
                                ->helperText('Indica si el contrato está vigente')
                                ->default(true)
                                ->inline(false),
                        ]),

                    Forms\Components\Textarea::make('observaciones_contrato')
                        ->label('Observaciones del Contrato')
                        ->placeholder('Ingrese cualquier observación relevante sobre el contrato')
                        ->maxLength(65535)
                        ->columnSpanFull(),
                ]),

            Wizard\Step::make('Especialidades')
                ->schema([
                    Forms\Components\CheckboxList::make('especialidades')
                        ->relationship('especialidades', 'especialidad')
                        ->required()
                        ->columns(2),
                ]),
                
            Wizard\Step::make('Usuario de Acceso')
                ->description('Configure los datos de acceso del médico al sistema')
                ->schema([
                    Forms\Components\Section::make('¿Crear usuario de acceso?')
                        ->description('Determine si este médico necesita acceso al sistema')
                        ->schema([
                            Forms\Components\Toggle::make('crear_usuario')
                                ->label('Crear usuario de acceso para este médico')
                                ->helperText('Active esta opción si el médico necesita acceder al sistema')
                                ->default(true)
                                ->live()
                                ->inline(false)
                                ->dehydrated(),
                        ]),
                        
                    Forms\Components\Section::make('Datos del Usuario')
                        ->description('Complete la información de acceso del médico')
                        ->schema([
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('username')
                                        ->label('Nombre de usuario')
                                        ->required(fn (Forms\Get $get) => $get('crear_usuario'))
                                        ->maxLength(255)
                                        ->placeholder('Ej: juan.perez')
                                        ->helperText('Usado para iniciar sesión en el sistema')
                                        ->live(debounce: 500)
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            // Auto-generar email basado en username si está vacío
                                            $set('user_email', strtolower($state) . '@clinica.com');
                                        })
                                        ->rules([
                                            'regex:/^[a-zA-Z0-9._-]+$/',
                                            function () {
                                                return function (string $attribute, $value, \Closure $fail) {
                                                    if (\App\Models\User::where('name', $value)->exists()) {
                                                        $fail('Este nombre de usuario ya está en uso.');
                                                    }
                                                };
                                            },
                                        ])
                                        ->dehydrated(),
                                        
                                    Forms\Components\TextInput::make('user_email')
                                        ->label('Email corporativo')
                                        ->email()
                                        ->required(fn (Forms\Get $get) => $get('crear_usuario'))
                                        ->maxLength(255)
                                        ->placeholder('Ej: juan.perez@clinica.com')
                                        ->helperText('Email para notificaciones y recuperación de contraseña')
                                        ->rules([
                                            function () {
                                                return function (string $attribute, $value, \Closure $fail) {
                                                    if (\App\Models\User::where('email', $value)->exists()) {
                                                        $fail('Este email ya está en uso.');
                                                    }
                                                };
                                            },
                                        ])
                                        ->dehydrated(),
                                ]),
                                
                            Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\TextInput::make('user_password')
                                        ->label('Contraseña')
                                        ->password()
                                        ->required(fn (Forms\Get $get) => $get('crear_usuario'))
                                        ->minLength(8)
                                        ->maxLength(255)
                                        ->placeholder('Mínimo 8 caracteres')
                                        ->helperText('Contraseña inicial del médico (puede cambiarla después)')
                                        ->dehydrated(),
                                        
                                    Forms\Components\TextInput::make('user_password_confirmation')
                                        ->label('Confirmar contraseña')
                                        ->password()
                                        ->required(fn (Forms\Get $get) => $get('crear_usuario'))
                                        ->same('user_password')
                                        ->placeholder('Repita la contraseña')
                                        ->helperText('Debe coincidir con la contraseña anterior')
                                        ->dehydrated(false), // No enviar al servidor
                                ]),
                                
                            Forms\Components\Select::make('user_role')
                                ->label('Rol en el sistema')
                                ->options([
                                    'medico' => 'Médico - Puede gestionar pacientes y consultas',
                                    'admin' => 'Administrador - Acceso completo al sistema',
                                    'recepcionista' => 'Recepcionista - Gestión de citas y pacientes',
                                ])
                                ->default('medico')
                                ->required(fn (Forms\Get $get) => $get('crear_usuario'))
                                ->helperText('Define los permisos del usuario en el sistema')
                                ->dehydrated(),
                                
                            Forms\Components\Toggle::make('user_active')
                                ->label('Usuario activo')
                                ->helperText('Determine si el usuario puede acceder inmediatamente')
                                ->default(true)
                                ->inline(false)
                                ->dehydrated(),
                                
                            
                        ])
                        ->visible(fn (Forms\Get $get) => $get('crear_usuario'))
                        ->columns(1),
                        
                    Forms\Components\Section::make('Generación Automática')
                        ->description('Opción rápida: generar datos automáticamente')
                        ->schema([
                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('auto_generate')
                                    ->label('🎲 Generar datos automáticamente')
                                    ->icon('heroicon-o-sparkles')
                                    ->color('info')
                                    ->action(function (callable $set, Forms\Get $get) {
                                        // Obtener nombre de los datos de persona
                                        $primerNombre = $get('primer_nombre');
                                        $primerApellido = $get('primer_apellido');
                                        
                                        if ($primerNombre && $primerApellido) {
                                            $username = strtolower($primerNombre . '.' . $primerApellido);
                                            $username = preg_replace('/[^a-z0-9.]/', '', $username);
                                            
                                            $email = $username . '@clinica.com';
                                            $password = 'Temp' . rand(1000, 9999);
                                            
                                            $set('username', $username);
                                            $set('user_email', $email);
                                            $set('user_password', $password);
                                            $set('user_password_confirmation', $password);
                                        }
                                    }),
                            ])
                        ])
                        ->visible(fn (Forms\Get $get) => $get('crear_usuario'))
                        ->collapsible()
                        ->collapsed(),
                ]),
        ])
        ->columnSpanFull() //  Esto hará que el Wizard ocupe el 100% del ancho
            ->nextAction(
                fn ($action) => $action->label('Siguiente')  // "Next" → "Siguiente"
            )
            

        ->persistStepInQueryString(),
    ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('persona.primer_nombre')
                    ->label('Nombre')
                    ->formatStateUsing(fn ($record) => 
                        "{$record->persona->primer_nombre} {$record->persona->primer_apellido}")
                    ->searchable(['primer_nombre', 'primer_apellido']),
                    
                Tables\Columns\TextColumn::make('persona.dni')
                    ->label('DNI')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('numero_colegiacion')
                    ->label('N° Colegiación')
                    ->searchable(),

                Tables\Columns\TextColumn::make('persona.telefono')
                    ->label('Teléfono')
                    ->searchable(),

                Tables\Columns\TextColumn::make('especialidades.especialidad')
                    ->label('Especialidades')
                    ->badge()
                    ->separator(',')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('horario_entrada')
                    ->label('Hora de Entrada')
                    ->time('g:i A'),

                Tables\Columns\TextColumn::make('horario_salida')
                    ->label('Hora de Salida')
                    ->time('g:i A'),

                Tables\Columns\IconColumn::make('persona.user.id')
                    ->label('Usuario')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn ($record) => $record->persona->user ? 'Tiene usuario: ' . $record->persona->user->name : 'Sin usuario de acceso'),
            ])
            ->filters([
                // Filtros opcionales
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Ver')
                        ->icon('heroicon-o-eye'),

                    Tables\Actions\EditAction::make()
                        ->label('Editar')
                        ->icon('heroicon-o-pencil'),

                    Tables\Actions\Action::make('crear_usuario')
                        ->label('Crear Usuario')
                        ->icon('heroicon-o-user-plus')
                        ->color('success')
                        ->visible(fn (Medico $record) => !$record->persona->user)
                        ->modalHeading('Crear Usuario de Acceso')
                        ->modalDescription('Complete los datos para crear un usuario de acceso al sistema para este médico')
                        ->form([
                            Forms\Components\Section::make('Datos del Usuario')
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('username')
                                                ->label('Nombre de usuario')
                                                ->required()
                                                ->maxLength(255)
                                                ->placeholder('Ej: juan.perez')
                                                ->helperText('Usado para iniciar sesión en el sistema')
                                                ->live(debounce: 500)
                                                ->afterStateUpdated(function ($state, callable $set) {
                                                    $set('user_email', strtolower($state) . '@clinica.com');
                                                })
                                                ->rules([
                                                    'regex:/^[a-zA-Z0-9._-]+$/',
                                                    function () {
                                                        return function (string $attribute, $value, \Closure $fail) {
                                                            if (\App\Models\User::where('name', $value)->exists()) {
                                                                $fail('Este nombre de usuario ya está en uso.');
                                                            }
                                                        };
                                                    },
                                                ]),
                                                
                                            Forms\Components\TextInput::make('user_email')
                                                ->label('Email corporativo')
                                                ->email()
                                                ->required()
                                                ->maxLength(255)
                                                ->placeholder('Ej: juan.perez@clinica.com')
                                                ->rules([
                                                    function () {
                                                        return function (string $attribute, $value, \Closure $fail) {
                                                            if (\App\Models\User::where('email', $value)->exists()) {
                                                                $fail('Este email ya está en uso.');
                                                            }
                                                        };
                                                    },
                                                ]),
                                        ]),
                                        
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('user_password')
                                                ->label('Contraseña')
                                                ->password()
                                                ->required()
                                                ->minLength(8)
                                                ->maxLength(255)
                                                ->placeholder('Mínimo 8 caracteres'),
                                                
                                            Forms\Components\TextInput::make('user_password_confirmation')
                                                ->label('Confirmar contraseña')
                                                ->password()
                                                ->required()
                                                ->same('user_password')
                                                ->placeholder('Repita la contraseña'),
                                        ]),
                                        
                                    Forms\Components\Select::make('user_role')
                                        ->label('Rol en el sistema')
                                        ->options([
                                            'medico' => 'Médico - Puede gestionar pacientes y consultas',
                                            'administrador centro' => 'Administrador Centro - Gestión completa del centro',
                                            'root' => 'Root - Acceso completo al sistema',
                                        ])
                                        ->default('medico')
                                        ->required(),
                                        
                                    Forms\Components\Toggle::make('user_active')
                                        ->label('Usuario activo')
                                        ->helperText('Determine si el usuario puede acceder inmediatamente')
                                        ->default(true)
                                        ->inline(false),
                                ])
                        ])
                        ->action(function (Medico $record, array $data) {
                            try {
                                // Crear el usuario
                                $user = \App\Models\User::create([
                                    'name' => $data['username'],
                                    'email' => $data['user_email'],
                                    'password' => Hash::make($data['user_password']),
                                    'persona_id' => $record->persona->id,
                                    'centro_id' => $centro_id,
                                    'email_verified_at' => $data['user_active'] ? now() : null,
                                ]);

                                // Asignar rol
                                $user->assignRole($data['user_role']);

                                Notification::make()
                                    ->title('✅ Usuario creado exitosamente')
                                    ->body("Usuario '{$data['username']}' creado para {$record->persona->primer_nombre} {$record->persona->primer_apellido}")
                                    ->success()
                                    ->persistent()
                                    ->send();

                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('❌ Error al crear usuario')
                                    ->body("Error: " . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->label('Eliminar')
                        ->icon('heroicon-o-trash')
                        ->modalHeading('Eliminar Médico')
                        ->modalDescription('¿Estás seguro de que deseas eliminar este médico y sus datos personales? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar')
                        ->modalCancelActionLabel('Cancelar')
                        ->action(function (Medico $record) {
                            DB::transaction(function () use ($record) {
                                $record->delete();
                                $record->persona()->delete();
                            });
                        })
                        ->successNotificationTitle('Médico y datos personales eliminados correctamente'),
                ])
                ->label('Opciones')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('success')
                ->button()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->searchPlaceholder('Buscar');
    }

    protected function getCreateFormAction(): PageAction
    {
        return PageAction::make('create')
            ->label('Crear Médico') // Texto personalizado del botón
            ->submit('create')
            ->keyBindings(['mod+s']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedicos::route('/'),
            'create' => Pages\CreateMedico::route('/create'),
            'view' => Pages\ViewMedico::route('/{record}'), //
            'edit' => Pages\EditMedico::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        // Obtener centro_id del usuario autenticado
        $centro_id = auth()->user()->centro_id;
        
        // Filtrar por el centro del usuario a menos que sea root
        if (!auth()->user()?->hasRole('root')) {
            $query->where('centro_id', $centro_id);
        }

        return $query;
    }

    public static function handleMedicoCreation(array $data): Medico
    {
        DB::beginTransaction();
        
        try {
            // Obtener el centro_id del usuario autenticado
            $centro_id = auth()->user()->centro_id ?? null;
            if (!$centro_id) {
                throw new \Exception('No se ha seleccionado un centro médico.');
            }
            
            $persona = Persona::where('dni', $data['dni'])->first();
            
            if (!$persona) {
                $persona = Persona::create([
                    'dni' => $data['dni'],
                    'primer_nombre' => $data['primer_nombre'],
                    'segundo_nombre' => $data['segundo_nombre'] ?? null,
                    'primer_apellido' => $data['primer_apellido'],
                    'segundo_apellido' => $data['segundo_apellido'] ?? null,
                    'telefono' => $data['telefono'] ?? null,
                    'direccion' => $data['direccion'] ?? null,
                    'sexo' => $data['sexo'],
                    'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
                    'nacionalidad_id' => $data['nacionalidad_id'] ?? null,
                ]);
            }

            // Crear el médico con el centro_id verificado
            $medico = Medico::create([
                'persona_id' => $persona->id,
                'numero_colegiacion' => $data['numero_colegiacion'],
                'horario_entrada' => $data['horario_entrada'],
                'horario_salida' => $data['horario_salida'],
                'centro_id' => $centro_id,
            ]);

            if (isset($data['especialidades'])) {
                $medico->especialidades()->sync($data['especialidades']);
            }

            // Crear el contrato médico
            if (isset($data['salario_quincenal']) && isset($data['porcentaje_servicio'])) {
                $contrato = \App\Models\ContabilidadMedica\ContratoMedico::create([
                    'medico_id' => $medico->id,
                    'salario_quincenal' => $data['salario_quincenal'],
                    'salario_mensual' => $data['salario_quincenal'] * 2,
                    'porcentaje_servicio' => $data['porcentaje_servicio'] ?? 0,
                    'fecha_inicio' => $data['fecha_inicio'],
                    'fecha_fin' => isset($data['fecha_fin']) && $data['fecha_fin'] ? $data['fecha_fin'] : null,
                    'activo' => $data['activo'] ?? true,
                    'centro_id' => $centro_id, // Usar la misma variable que usamos para el médico
                ]);
            }

            // Crear usuario si se ha solicitado
            if (isset($data['crear_usuario']) && $data['crear_usuario']) {
                try {
                    $user = \App\Models\User::create([
                        'name' => $data['username'],
                        'email' => $data['user_email'],
                        'password' => Hash::make($data['user_password']),
                        'persona_id' => $persona->id,
                        'centro_id' => session('current_centro_id'),
                        'email_verified_at' => $data['user_active'] ? now() : null,
                    ]);

                    // Asignar rol
                    $user->assignRole($data['user_role']);

                    Notification::make()
                        ->title('✅ Usuario creado exitosamente')
                        ->body("Usuario '{$data['username']}' creado para {$persona->primer_nombre} {$persona->primer_apellido}")
                        ->success()
                        ->persistent()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('❌ Error al crear usuario')
                        ->body("Error: " . $e->getMessage())
                        ->danger()
                        ->send();
                }
            }

            DB::commit();
            
            return $medico;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}






