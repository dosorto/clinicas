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
use Filament\Notifications\Notification;
use Illuminate\Validation\Rule;


class PacientesResource extends Resource
{
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('crear pacientes');
    }
    
    protected static ?string $model = Pacientes::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
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
                                ->label('DNI/Cédula *')
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
                                            $set('persona_id', $existingPersona->id);
                                            
                                            Notification::make()
                                                ->title('Persona encontrada')
                                                ->body("Se encontró: {$existingPersona->nombre_completo}")
                                                ->success()
                                                ->send();
                                        } else {
                                            $set('persona_id', null);
                                        }
                                    }
                                }),
                            
                            Forms\Components\Hidden::make('persona_id'),
                            /*Forms\Components\Hidden::make('centro_id')
                            ->default(fn () => auth()->user()->centro_id)
                            ->dehydrated(true),*/
                            
                            Forms\Components\TextInput::make('primer_nombre')
                                ->label('Primer Nombre *')
                                ->required()
                                ->maxLength(255)
                                ->reactive()
                                ->afterStateUpdated(fn ($state, callable $set) => self::checkPersonExists($state, $set)),
                            
                            Forms\Components\TextInput::make('segundo_nombre')
                                ->label('Segundo Nombre')
                                ->maxLength(255),
                            
                            Forms\Components\TextInput::make('primer_apellido')
                                ->label('Primer Apellido *')
                                ->required()
                                ->maxLength(255)
                                ->reactive()
                                ->afterStateUpdated(fn ($state, callable $set) => self::checkPersonExists($state, $set)),
                            
                            Forms\Components\TextInput::make('segundo_apellido')
                                ->label('Segundo Apellido')
                                ->maxLength(255),
                            
                            Forms\Components\TextInput::make('telefono')
                                ->label('Teléfono *')
                                ->required()
                                ->maxLength(255),
                            
                            Forms\Components\Textarea::make('direccion')
                                ->label('Dirección *')
                                ->required()
                                ->rows(3),
                            
                            Forms\Components\Select::make('sexo')
                                ->label('Sexo *')
                                ->options([
                                    'M' => 'Masculino',
                                    'F' => 'Femenino',
                                ])
                                ->required(),
                            
                            Forms\Components\DatePicker::make('fecha_nacimiento')
                                ->label('Fecha de Nacimiento *')
                                ->required()
                                ->native(false),
                                
                            Forms\Components\Select::make('nacionalidad_id')
                                ->label('Nacionalidad *')
                                ->options(Nacionalidad::pluck('nacionalidad', 'id'))
                                ->required()
                                ->searchable(),
                                
                            Forms\Components\FileUpload::make('foto')
                                ->label('Fotografía')
                                ->image()
                                ->directory('personas/fotos')
                                ->visibility('public')
                                ->imageEditor()
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->afterValidation(function (callable $get) {
                            // Validar que todos los campos obligatorios estén llenos
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
                                ->label('Grupo Sanguíneo *')
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
                                ->label('Contacto de Emergencia *')
                                ->required()
                                ->maxLength(255),
                        ])
                        ->columns(2)
                        ->afterValidation(function (callable $get) {
                            // Validar campos obligatorios del paciente
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
                            Forms\Components\Select::make('enfermedad_id')
                                ->label('Enfermedad *')
                                ->options(Enfermedade::all()->pluck('enfermedades', 'id'))
                                ->searchable()
                                ->preload()
                                ->required(),
                            
                            Forms\Components\DatePicker::make('fecha_diagnostico')
                                ->label('Fecha de Diagnóstico *')
                                ->default(now())
                                ->required()
                                ->native(false),
                            
                            Forms\Components\Textarea::make('tratamiento')
                                ->label('Tratamiento')
                                ->rows(3)
                                ->columnSpanFull()
                                ->required() 
                                ->dehydrated(true)
                        ])
                        ->columns(2)
                        ->afterValidation(function (callable $get) {
                            // Validar campos obligatorios de enfermedades
                            $requiredFields = [
                                'enfermedad_id' => 'Enfermedad',
                                'fecha_diagnostico' => 'Fecha de Diagnóstico',
                                'tratamiento' => 'Tratamiento',
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
                ])
                ->columnSpanFull()
                ->persistStepInQueryString(),
            ]);
    }

    protected static function checkPersonExists($state, callable $set)
    {
        // Lógica de verificación si es necesario
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('persona.foto')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-avatar.png')),
                
                Tables\Columns\TextColumn::make('persona.primer_nombre')
                    ->label('Nombre')
                    ->formatStateUsing(fn ($record) => 
                        "{$record->persona->primer_nombre} {$record->persona->primer_apellido}")
                    ->searchable(['primer_nombre', 'primer_apellido']),
                    
                Tables\Columns\TextColumn::make('persona.dni')
                    ->label('DNI')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('grupo_sanguineo')
                    ->label('Grupo Sanguíneo')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('contacto_emergencia')
                    ->label('Contacto Emergencia')
                    ->limit(30),
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