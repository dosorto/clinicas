<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PacientesResource\Pages;
use App\Models\Pacientes;
use App\Models\Persona;
use App\Models\Nacionalidad;
use App\Models\Enfermedade;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rule;
use Filament\Forms\Components\Section;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PacientesResource extends Resource
{
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('crear pacientes');
    }
    
    protected static ?string $model = Pacientes::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Gestión de Personas';
    protected static ?string $navigationLabel = 'Pacientes';
    protected static ?string $modelLabel = 'Paciente';
    protected static ?string $pluralModelLabel = 'Pacientes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Datos Personales')
                        ->schema([
                            // DNI COMO PRIMER CAMPO
                            Forms\Components\TextInput::make('dni')
                                ->label('DNI/Cédula')
                                ->required()
                                ->maxLength(255)
                                ->reactive()
                                ->disabled(fn ($operation) => $operation === 'edit')
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    if ($state) {
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
                                            
                                            // Handle file upload properly
                                            if ($existingPersona->fotografia) {
                                                $set('fotografia', [
                                                    'path' => $existingPersona->fotografia,
                                                    'name' => basename($existingPersona->fotografia),
                                                    'size' => Storage::disk('public')->size($existingPersona->fotografia),
                                                    'type' => Storage::disk('public')->mimeType($existingPersona->fotografia),
                                                ]);
                                            } else {
                                                $set('fotografia', null);
                                            }
                                            
                                            $set('persona_id', $existingPersona->id);
                                            
                                            Notification::make()
                                                ->title('Persona encontrada')
                                                ->body("Se encontró: {$existingPersona->primer_nombre} {$existingPersona->primer_apellido}")
                                                ->success()
                                                ->send();
                                        } else {
                                            $set('persona_id', null);
                                            $set('fotografia', null);
                                        }
                                    }
                                }),
                            
                            Forms\Components\Hidden::make('persona_id'),
                            
                            // ✅ VALIDACIÓN DE SOLO LETRAS PARA NOMBRES
                            Forms\Components\TextInput::make('primer_nombre')
                                ->label('Primer Nombre')
                                ->required()
                                ->maxLength(255)
                                ->reactive()
                                ->rules(['regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'])
                                ->validationMessages([
                                    'regex' => 'El primer nombre solo puede contener letras.',
                                ])
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $state)) {
                                        Notification::make()
                                            ->title('Error de validación')
                                            ->body('El primer nombre solo puede contener letras.')
                                            ->danger()
                                            ->send();
                                    }
                                    self::checkPersonExists($state, $set);
                                }),
                            
                            Forms\Components\TextInput::make('segundo_nombre')
                                ->label('Segundo Nombre')
                                ->maxLength(255)
                                ->rules(['regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/'])
                                ->validationMessages([
                                    'regex' => 'El segundo nombre solo puede contener letras.',
                                ])
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/', $state)) {
                                        Notification::make()
                                            ->title('Error de validación')
                                            ->body('El segundo nombre solo puede contener letras.')
                                            ->danger()
                                            ->send();
                                    }
                                }),
                            
                            Forms\Components\TextInput::make('primer_apellido')
                                ->label('Primer Apellido')
                                ->required()
                                ->maxLength(255)
                                ->reactive()
                                ->rules(['regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'])
                                ->validationMessages([
                                    'regex' => 'El primer apellido solo puede contener letras.',
                                ])
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $state)) {
                                        Notification::make()
                                            ->title('Error de validación')
                                            ->body('El primer apellido solo puede contener letras.')
                                            ->danger()
                                            ->send();
                                    }
                                    self::checkPersonExists($state, $set);
                                }),
                            
                            Forms\Components\TextInput::make('segundo_apellido')
                                ->label('Segundo Apellido')
                                ->maxLength(255)
                                ->rules(['regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/'])
                                ->validationMessages([
                                    'regex' => 'El segundo apellido solo puede contener letras.',
                                ])
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/', $state)) {
                                        Notification::make()
                                            ->title('Error de validación')
                                            ->body('El segundo apellido solo puede contener letras.')
                                            ->danger()
                                            ->send();
                                    }
                                }),
                            
                            Forms\Components\TextInput::make('telefono')
                                ->label('Teléfono')
                                ->required()
                                ->maxLength(255),
                            
                            Forms\Components\Textarea::make('direccion')
                                ->label('Dirección')
                                ->required()
                                ->rows(3),
                            
                            Forms\Components\Select::make('sexo')
                                ->label('Sexo')
                                ->options([
                                    'M' => 'Masculino',
                                    'F' => 'Femenino',
                                ])
                                ->required(),
                            
                            Forms\Components\DatePicker::make('fecha_nacimiento')
                                ->label('Fecha de Nacimiento')
                                ->required()
                                ->native(false),
                                
                            Forms\Components\Select::make('nacionalidad_id')
                                ->label('Nacionalidad')
                                ->options(Nacionalidad::pluck('nacionalidad', 'id'))
                                ->required()
                                ->searchable(),
                                
                            // ✅ MEJORAR SUBIDA DE ARCHIVOS
                            Forms\Components\FileUpload::make('fotografia')
                                ->label('Fotografía')
                                ->image()
                                ->directory('personas/fotos')
                                ->disk('public')
                                ->visibility('public')
                                ->imageEditor()
                                ->maxSize(2048)
                                ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'])
                                ->preserveFilenames(false) // ✅ CAMBIO: No preservar nombres originales
                                ->getUploadedFileNameForStorageUsing(function ($file) {
                                    // ✅ GENERAR NOMBRE ÚNICO Y LIMPIO
                                    $extension = $file->getClientOriginalExtension();
                                    $timestamp = now()->format('YmdHis');
                                    $random = Str::random(8);
                                    return "foto_{$timestamp}_{$random}.{$extension}";
                                })
                                ->downloadable()
                                ->openable()
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->afterValidation(function (callable $get) {
                            $requiredFields = [
                                'dni' => 'DNI/Cédula',
                                'primer_nombre' => 'Primer Nombre',
                                'primer_apellido' => 'Primer Apellido',
                                'telefono' => 'Teléfono',
                                'direccion' => 'Dirección',
                                'sexo' => 'Sexo',
                                'fecha_nacimiento' => 'Fecha de Nacimiento',
                                'nacionalidad_id' => 'Nacionalidad',
                            ];
                            
                            $missingFields = [];
                            foreach ($requiredFields as $field => $label) {
                                if (empty($get($field))) {
                                    $missingFields[] = $label;
                                }
                            }
                            
                            if (!empty($missingFields)) {
                                Notification::make()
                                    ->title('Campos obligatorios faltantes')
                                    ->body('Complete los siguientes campos: ' . implode(', ', $missingFields))
                                    ->danger()
                                    ->send();
                                    
                                throw new \Exception('Campos obligatorios faltantes');
                            }
                        }),

                    Wizard\Step::make('Datos del Paciente')
                        ->schema([
                            Forms\Components\Select::make('grupo_sanguineo')
                                ->label('Grupo Sanguíneo')
                                ->options([
                                    'A+' => 'A+',
                                    'A-' => 'A-',
                                    'B+' => 'B+',
                                    'B-' => 'B-',
                                    'O+' => 'O+',
                                    'O-' => 'O-',
                                    'AB+' => 'AB+',
                                    'AB-' => 'AB-',
                                ])
                                ->required(),
                            
                            Forms\Components\TextInput::make('contacto_emergencia')
                                ->label('Contacto de Emergencia')
                                ->required()
                                ->maxLength(255),
                        ])
                        ->columns(2)
                        ->afterValidation(function (callable $get) {
                            $requiredFields = [
                                'grupo_sanguineo' => 'Grupo Sanguíneo',
                                'contacto_emergencia' => 'Contacto de Emergencia',
                            ];
                            
                            $missingFields = [];
                            foreach ($requiredFields as $field => $label) {
                                if (empty($get($field))) {
                                    $missingFields[] = $label;
                                }
                            }
                            
                            if (!empty($missingFields)) {
                                Notification::make()
                                    ->title('Campos obligatorios faltantes')
                                    ->body('Complete los siguientes campos: ' . implode(', ', $missingFields))
                                    ->danger()
                                    ->send();
                                    
                                throw new \Exception('Campos obligatorios faltantes');
                            }
                        }),
                    
                    Wizard\Step::make('Enfermedades')
                        ->schema([
                            Repeater::make('enfermedades_data')
                                ->label('Enfermedades del Paciente')
                                ->schema([
                                    Forms\Components\Select::make('enfermedad_id')
                                        ->label('Enfermedad')
                                        ->options(Enfermedade::all()->pluck('enfermedades', 'id'))
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                            $enfermedadesData = $get('../../enfermedades_data') ?? [];
                                            $enfermedadesSeleccionadas = array_filter(
                                                array_column($enfermedadesData, 'enfermedad_id'),
                                                fn($id) => !is_null($id)
                                            );
                                            
                                            $repetidas = array_count_values($enfermedadesSeleccionadas);
                                            if (isset($repetidas[$state]) && $repetidas[$state] > 1) {
                                                Notification::make()
                                                    ->title('Enfermedad duplicada')
                                                    ->body('No puede seleccionar la misma enfermedad más de una vez.')
                                                    ->danger()
                                                    ->send();
                                                $set('enfermedad_id', null);
                                            }
                                        }),
                                    
                                    Forms\Components\TextInput::make('ano_diagnostico')
                                        ->label('Año de Diagnóstico')
                                        ->numeric()
                                        ->minValue(1900)
                                        ->maxValue(date('Y'))
                                        ->default(date('Y'))
                                        ->required(),
                                    
                                    Forms\Components\Textarea::make('tratamiento')
                                        ->label('Tratamiento')
                                        ->rows(3)
                                        ->columnSpanFull()
                                        ->required()
                                ])
                                ->columns(2)
                                ->defaultItems(1)
                                ->addActionLabel('Agregar Enfermedad')
                                ->itemLabel(fn (array $state): ?string => 
                                    $state['enfermedad_id'] ? 
                                    Enfermedade::find($state['enfermedad_id'])?->enfermedades : 
                                    'Nueva Enfermedad'
                                )
                                ->collapsible()
                                ->cloneable()
                                ->reorderable()
                                ->deleteAction(
                                    fn (Forms\Components\Actions\Action $action) => $action
                                        ->requiresConfirmation()
                                        ->modalDescription('¿Estás seguro de que deseas eliminar esta enfermedad?')
                                )
                                ->minItems(1)
                                ->columnSpanFull()
                        ])
                        ->afterValidation(function (callable $get) {
                            $enfermedadesData = $get('enfermedades_data');
                            if (empty($enfermedadesData)) {
                                Notification::make()
                                    ->title('Error de validación')
                                    ->body('Debe agregar al menos una enfermedad')
                                    ->danger()
                                    ->send();
                                    
                                throw new \Exception('Debe agregar al menos una enfermedad');
                            }
                            
                            $enfermedadesSeleccionadas = array_filter(
                                array_column($enfermedadesData, 'enfermedad_id'),
                                fn($id) => !is_null($id)
                            );
                            
                            if (count($enfermedadesSeleccionadas) !== count(array_unique($enfermedadesSeleccionadas))) {
                                Notification::make()
                                    ->title('Enfermedades duplicadas')
                                    ->body('No puede seleccionar la misma enfermedad más de una vez.')
                                    ->danger()
                                    ->send();
                                    
                                throw new \Exception('Enfermedades duplicadas detectadas');
                            }
                        }),
                ])
                ->columnSpanFull()
                ->persistStepInQueryString(),
            ]);
    }

    protected static function checkPersonExists($state, callable $set)
    {
        // Lógica de verificación si es necesario
    }

    public static function generateAvatar($nombre, $apellido)
    {
        $iniciales = strtoupper(substr($nombre, 0, 1) . substr($apellido, 0, 1));
        $colores = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7', '#DDA0DD', '#98D8C8'];
        $color = $colores[array_rand($colores)];
        
        return "https://ui-avatars.com/api/?name={$iniciales}&background=" . substr($color, 1) . "&color=fff&size=100&font-size=0.5";
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('persona.fotografia')
                    ->label('Foto')
                    ->circular()
                    ->size(50)
                    ->getStateUsing(function ($record) {
                        if ($record->persona->fotografia) {
                            return asset('storage/' . $record->persona->fotografia);
                        }
                        return self::generateAvatar(
                            $record->persona->primer_nombre,
                            $record->persona->primer_apellido
                        );
                    }),
                
                Tables\Columns\TextColumn::make('persona.primer_nombre')
                    ->label('Nombre Completo')
                    ->formatStateUsing(fn ($record) => 
                        trim("{$record->persona->primer_nombre} {$record->persona->segundo_nombre} {$record->persona->primer_apellido} {$record->persona->segundo_apellido}"))
                    ->searchable(['primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido'])
                    ->weight(FontWeight::Medium),
                    
                Tables\Columns\TextColumn::make('persona.dni')
                    ->label('DNI')
                    ->searchable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('grupo_sanguineo')
                    ->label('Grupo Sanguíneo')
                    ->sortable()
                    ->badge()
                    ->color('danger'),
                
                Tables\Columns\TextColumn::make('contacto_emergencia')
                    ->label('Contacto Emergencia')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->contacto_emergencia),
                
                Tables\Columns\TextColumn::make('enfermedades')
                    ->label('Enfermedades')
                    ->formatStateUsing(function ($record) {
                        $enfermedades = $record->enfermedades->pluck('enfermedades')->toArray();
                        if (empty($enfermedades)) {
                            return 'Sin enfermedades';
                        }
                        return implode(', ', array_slice($enfermedades, 0, 2)) . 
                               (count($enfermedades) > 2 ? '...' : '');
                    })
                    ->tooltip(function ($record) {
                        $enfermedades = $record->enfermedades->pluck('enfermedades')->toArray();
                        return implode(', ', $enfermedades);
                    })
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('grupo_sanguineo')
                    ->label('Grupo Sanguíneo')
                    ->options([
                        'A+' => 'A+',
                        'A-' => 'A-',
                        'B+' => 'B+',
                        'B-' => 'B-',
                        'O+' => 'O+',
                        'O-' => 'O-',
                        'AB+' => 'AB+',
                        'AB-' => 'AB-',
                    ]),
                
                Tables\Filters\SelectFilter::make('sexo')
                    ->label('Sexo')
                    ->options([
                        'M' => 'Masculino',
                        'F' => 'Femenino',
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value']) {
                            $query->whereHas('persona', function ($q) use ($data) {
                                $q->where('sexo', $data['value']);
                            });
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Información Personal')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\ImageEntry::make('persona.fotografia')
                                        ->label('Fotografía')
                                        ->circular()
                                        ->size(150)
                                        ->getStateUsing(function ($record) {
                                            if ($record->persona->fotografia) {
                                                return asset('storage/' . $record->persona->fotografia);
                                            }
                                            return self::generateAvatar(
                                                $record->persona->primer_nombre,
                                                $record->persona->primer_apellido
                                            );
                                        }),
                                ]),
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\TextEntry::make('persona.primer_nombre')
                                        ->label('Primer Nombre')
                                        ->weight(FontWeight::Bold),
                                    Infolists\Components\TextEntry::make('persona.segundo_nombre')
                                        ->label('Segundo Nombre'),
                                    Infolists\Components\TextEntry::make('persona.primer_apellido')
                                        ->label('Primer Apellido')
                                        ->weight(FontWeight::Bold),
                                    Infolists\Components\TextEntry::make('persona.segundo_apellido')
                                        ->label('Segundo Apellido'),
                                    Infolists\Components\TextEntry::make('persona.dni')
                                        ->label('DNI/Cédula')
                                        ->copyable(),
                                    Infolists\Components\TextEntry::make('persona.sexo')
                                        ->label('Sexo')
                                        ->formatStateUsing(fn ($state) => $state === 'M' ? 'Masculino' : 'Femenino'),
                                    Infolists\Components\TextEntry::make('persona.fecha_nacimiento')
                                        ->label('Fecha de Nacimiento')
                                        ->date('d/m/Y'),
                                    Infolists\Components\TextEntry::make('persona.telefono')
                                        ->label('Teléfono')
                                        ->copyable(),
                                    Infolists\Components\TextEntry::make('persona.direccion')
                                        ->label('Dirección')
                                        ->columnSpanFull(),
                                    Infolists\Components\TextEntry::make('persona.nacionalidad.nacionalidad')
                                        ->label('Nacionalidad'),
                                ]),
                        ]),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Información Médica')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('grupo_sanguineo')
                                    ->label('Grupo Sanguíneo')
                                    ->badge()
                                    ->color('danger'),
                                Infolists\Components\TextEntry::make('contacto_emergencia')
                                    ->label('Contacto de Emergencia')
                                    ->copyable(),
                            ]),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Enfermedades')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('enfermedades')
                            ->schema([
                                Infolists\Components\Grid::make(3)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('enfermedades')
                                            ->label('Enfermedad')
                                            ->weight(FontWeight::Bold),
                                        Infolists\Components\TextEntry::make('pivot.fecha_diagnostico')
                                            ->label('Año de Diagnóstico')
                                            ->formatStateUsing(fn ($state) => $state ? date('Y', strtotime($state)) : 'N/A'),
                                        Infolists\Components\TextEntry::make('pivot.tratamiento')
                                            ->label('Tratamiento')
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->contained(false),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('Información del Sistema')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Registrado')
                                    ->dateTime('d/m/Y H:i'),
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Última Actualización')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPacientes::route('/'),
            'create' => Pages\CreatePacientes::route('/create'),
            'view' => Pages\ViewPacientes::route('/{record}'),
            'edit' => Pages\EditPacientes::route('/{record}/edit'),
        ];
    }
}