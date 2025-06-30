<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PacientesResource\Pages;
use App\Models\Pacientes;
use App\Models\Persona;
use App\Models\Nacionalidad;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Notifications\Notification;

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
                Fieldset::make('Datos Personales')
                    ->schema([
                        TextInput::make('primer_nombre')
                            ->label('Primer Nombre')
                            ->required()
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => self::checkPersonExists($state, $set)),
                        
                        TextInput::make('segundo_nombre')
                            ->label('Segundo Nombre')
                            ->nullable()
                            ->maxLength(255),
                        
                        TextInput::make('primer_apellido')
                            ->label('Primer Apellido')
                            ->required()
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => self::checkPersonExists($state, $set)),
                        
                        TextInput::make('segundo_apellido')
                            ->label('Segundo Apellido')
                            ->nullable()
                            ->maxLength(255),
                        
                        TextInput::make('dni')
                            ->label('DNI/Cédula')
                            ->required()
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state) {
                                    $existingPersona = Persona::where('dni', $state)->first();
                                    if ($existingPersona) {
                                        // Llenar automáticamente los campos con datos existentes
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
                        
                        TextInput::make('telefono')
                            ->label('Teléfono')
                            ->nullable()
                            ->maxLength(255),
                        
                        Textarea::make('direccion')
                            ->label('Dirección')
                            ->nullable()
                            ->rows(3),
                        
                        Select::make('sexo')
                            ->label('Sexo')
                            ->options([
                                'M' => 'M',
                                'F' => 'F',
                            ])
                            ->required(),
                        
                        DatePicker::make('fecha_nacimiento')
                            ->label('Fecha de Nacimiento')
                            ->nullable()
                            ->native(false),
                            
                        Select::make('nacionalidad_id')
                            ->label('Nacionalidad')
                            ->options(\App\Models\Nacionalidad::pluck('nacionalidad', 'id')->toArray())
                            ->searchable()
                            ->nullable(),
                    ])
                    ->columns(2),

                Fieldset::make('Datos del Paciente')
                    ->schema([
                        Select::make('grupo_sanguineo')
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
                            ->nullable(),
                        
                        TextInput::make('contacto_emergencia')
                            ->label('Contacto de Emergencia')
                            ->nullable()
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }

    // Método para verificar si existe persona
    protected static function checkPersonExists($state, callable $set)
    {
        // Este método se puede expandir para verificaciones adicionales
        // Por ahora, la verificación principal está en el campo DNI
    }

    // Método para crear o encontrar persona y crear paciente
    public static function createPacienteWithPersona(array $data): Pacientes
    {
        // Separar datos de persona y paciente
        $personaData = [
            'primer_nombre' => $data['primer_nombre'],
            'segundo_nombre' => $data['segundo_nombre'] ?? null,
            'primer_apellido' => $data['primer_apellido'],
            'segundo_apellido' => $data['segundo_apellido'] ?? null,
            'dni' => $data['dni'],
            'telefono' => $data['telefono'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'sexo' => $data['sexo'],
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'nacionalidad_id' => $data['nacionalidad_id'] ?? null,
            'created_by' => 1,
        ];

        $pacienteData = [
            'grupo_sanguineo' => $data['grupo_sanguineo'] ?? null,
            'contacto_emergencia' => $data['contacto_emergencia'] ?? null,
        ];

        // Buscar persona existente
        $persona = null;
        
        // Verificar por DNI primero
        if (!empty($personaData['dni'])) {
            $persona = Persona::where('dni', $personaData['dni'])->first();
        }
        
        // Si no existe, crear nueva persona
        if (!$persona) {
            $persona = Persona::create($personaData);
        }

        // Verificar si ya existe un paciente para esta persona
        $existingPaciente = Pacientes::where('persona_id', $persona->id)->first();
        
        if ($existingPaciente) {
            // Actualizar datos del paciente existente
            $existingPaciente->update($pacienteData);
            return $existingPaciente;
        }

        // Crear nuevo paciente
        $pacienteData['persona_id'] = $persona->id;
        return Pacientes::create($pacienteData);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('persona.primer_nombre')
                    ->label('Primer Nombre')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('persona.primer_apellido')
                    ->label('Primer Apellido')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('persona.dni')
                    ->label('DNI')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('persona.telefono')
                    ->label('Teléfono')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('grupo_sanguineo')
                    ->label('Grupo Sanguíneo')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('contacto_emergencia')
                    ->label('Contacto de Emergencia')
                    ->limit(30),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
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