<?php
/*
namespace App\Filament\Resources\Medico;

use App\Filament\Resources\Medico\MedicoResource\Pages;
use App\Filament\Resources\Medico\MedicoResource\RelationManagers;
use App\Models\Medico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MedicoResource extends Resource
{
    
    protected static ?string $model = Medico::class;


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Select::make('persona_id')
                ->label('Persona')
                ->relationship('persona', 'primer_nombre')
                ->searchable()
                ->preload()
                ->required(),
                
            Forms\Components\TextInput::make('numero_colegiacion')
                ->label('Número de Colegiación')
                ->required()
                ->maxLength(50),
        ]);
    }

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('persona.primer_nombre')
                ->label('Nombre Completo')
                ->getStateUsing(fn ($record) => $record->persona->primer_nombre.' '.$record->persona->primer_apellido)
                ->searchable(['primer_nombre', 'primer_apellido']),
                
            Tables\Columns\TextColumn::make('numero_colegiacion')
                ->label('N° Colegiación')
                ->searchable(),
        ])
        ->filters([
            //
        ])
        ->actions([
            Tables\Actions\EditAction::make()
                ->icon('heroicon-o-pencil') // Icono de edición
                ->color('primary'),
                
            Tables\Actions\DeleteAction::make()
                ->icon('heroicon-o-trash') // Icono de borrado
                ->color('danger'),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
}

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedicos::route('/'),
            'create' => Pages\CreateMedico::route('/create'),
            'edit' => Pages\EditMedico::route('/{record}/edit'),
        ];
    }
};

*/

/*

namespace App\Filament\Resources\Medico;

use App\Filament\Resources\Medico\MedicoResource\Pages;
use App\Models\Persona;
use App\Models\Medico;
use App\Models\Nacionalidad;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;

class MedicoResource extends Resource
{
    protected static ?string $model = Medico::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationLabel = 'Médicos';
    protected static ?string $modelLabel = 'Médico';
    protected static ?string $pluralModelLabel = 'Médicos';

public static function form(Form $form): Form
{
    return $form
        ->schema([
            // Datos Personales
            Fieldset::make('Datos Personales')
                ->schema([
                    TextInput::make('primer_nombre')
                        ->label('Primer Nombre')
                        ->required()
                        ->maxLength(255),
                    
                    TextInput::make('segundo_nombre')
                        ->label('Segundo Nombre')
                        ->maxLength(255)
                        ->nullable(),
                    
                    TextInput::make('primer_apellido')
                        ->label('Primer Apellido')
                        ->required()
                        ->maxLength(255),
                    
                    TextInput::make('segundo_apellido')
                        ->label('Segundo Apellido')
                        ->maxLength(255)
                        ->nullable(),
                    
                    TextInput::make('dni')
                        ->label('DNI/Cédula')
                        ->required()
                        ->maxLength(255)
                        ->unique('personas', 'dni', ignoreRecord: true),
                    
                    TextInput::make('telefono')
                        ->label('Teléfono')
                        ->maxLength(255)
                        ->nullable(),
                    
                    Textarea::make('direccion')
                        ->label('Dirección')
                        ->nullable()
                        ->columnSpanFull(),
                    
                    Select::make('sexo')
                        ->label('Sexo')
                        ->options([
                            'M' => 'Masculino',
                            'F' => 'Femenino',
                        ])
                        ->required(),
                    
                    DatePicker::make('fecha_nacimiento')
                        ->label('Fecha de Nacimiento')
                        ->native(false)
                        ->nullable(),
                        
                    Select::make('nacionalidad_id')
                        ->label('Nacionalidad')
                        ->options(Nacionalidad::pluck('nacionalidad', 'id'))
                        ->searchable()
                        ->nullable(),
                ])
                ->columns(2),

            // Datos Profesionales
            Fieldset::make('Datos Profesionales')
                ->schema([
                    TextInput::make('numero_colegiacion')
                        ->label('Número de Colegiación')
                        ->required()
                        ->maxLength(50)
                        ->unique('medicos', 'numero_colegiacion', ignoreRecord: true),
                ])
                ->columns(2),
                
            // Campo oculto para persona_id (se llenará automáticamente)
            Forms\Components\Hidden::make('persona_id'),
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
            ])
            ->filters([
                // Filtros opcionales
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->color('primary'),
                    
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->color('danger'),
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
            'index' => Pages\ListMedicos::route('/'),
            'create' => Pages\CreateMedico::route('/create'),
            'edit' => Pages\EditMedico::route('/{record}/edit'),
        ];
    }

    // Método para manejar la creación del médico con los datos de persona
    public static function handleMedicoCreation(array $data): Medico
    {
        // Primero creamos o actualizamos la persona
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
        ];

        // Buscar persona existente por DNI
        $persona = Persona::where('dni', $data['dni'])->first();

        if ($persona) {
            $persona->update($personaData);
        } else {
            $persona = Persona::create($personaData);
        }

        // Datos específicos del médico
        $medicoData = [
            'numero_colegiacion' => $data['numero_colegiacion'],
            'persona_id' => $persona->id,
        ];

        // Verificar si ya existe un médico para esta persona
        $existingMedico = Medico::where('persona_id', $persona->id)->first();

        if ($existingMedico) {
            $existingMedico->update($medicoData);
            return $existingMedico;
        }

        // Crear nuevo médico
        return Medico::create($medicoData);
    }
}

// */

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

class MedicoResource extends Resource
{
    protected static ?string $model = Medico::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationLabel = 'Médicos';
    protected static ?string $modelLabel = 'Médico';
    protected static ?string $pluralModelLabel = 'Médicos';

    public static function form(Form $form): Form
    {
return $form
    ->schema([
        Wizard::make([
            Wizard\Step::make('Datos Personales')
                ->schema([
                    Forms\Components\TextInput::make('primer_nombre')
                        ->label('Primer Nombre')
                        ->required()
                        ->maxLength(255),
                        
                    Forms\Components\TextInput::make('segundo_nombre')
                        ->label('Segundo Nombre')
                        ->maxLength(255)
                        ->nullable(),
                        
                    Forms\Components\TextInput::make('primer_apellido')
                        ->label('Primer Apellido')
                        ->required()
                        ->maxLength(255),
                        
                    Forms\Components\TextInput::make('segundo_apellido')
                        ->label('Segundo Apellido')
                        ->maxLength(255)
                        ->nullable(),
                        
                    Forms\Components\TextInput::make('dni')
                        ->label('DNI/Cédula')
                        ->required()
                        ->maxLength(255)
                        ->disabled(fn ($operation) => $operation === 'edit') // Deshabilitar en edición
                        ->dehydrated() // Mantener el valor al enviar el formulario
                        ->rules([
                            function (Get $get) {
                                return function (string $attribute, $value, Closure $fail) use ($get) {
                                    // Solo validar durante creación
                                    if ($get('id') === null) {
                                        $exists = Persona::where('dni', $value)->exists();
                                        if ($exists) {
                                            $fail('Este DNI ya está registrado por otra persona');
                                        }
                                    }
                                };
                            },
                        ]),
                        
                    Forms\Components\TextInput::make('telefono')
                        ->label('Teléfono')
                        ->maxLength(255)
                        ->required(),
                        
                    Forms\Components\Textarea::make('direccion')
                        ->label('Dirección')
                        ->nullable()
                        ->columnSpanFull(),
                        
                    Forms\Components\Select::make('sexo')
                        ->label('Sexo')
                        ->options([
                            'M' => 'Masculino',
                            'F' => 'Femenino',
                        ])
                        ->required(),
                        
                    Forms\Components\DatePicker::make('fecha_nacimiento')
                        ->label('Fecha de Nacimiento')
                        ->native(false)
                        ->required(),
                    Forms\Components\FileUpload::make('persona.foto')
                    ->label('Fotografía')
                    ->image()
                    ->directory('personas/fotos') // Carpeta donde se guardarán las imágenes
                    ->visibility('public') // O 'private' según tus necesidades
                    ->imageEditor() // Opcional: permite recortar/editar la imagen
                    ->columnSpanFull(),
                        
                    Forms\Components\Select::make('nacionalidad_id')
                        ->label('Nacionalidad')
                        ->options(Nacionalidad::pluck('nacionalidad', 'id'))
                        ->searchable()
                        ->required(),
                ])
                ->columns(2),

                
                
            Wizard\Step::make('Datos Profesionales')
                ->schema([
                    Forms\Components\TextInput::make('numero_colegiacion')
                        ->label('Número de Colegiación')
                        ->required()
                        ->maxLength(50)
                        ->unique('medicos', 'numero_colegiacion', ignoreRecord: true),
                ]),
                
            Wizard\Step::make('Especialidades')
                ->schema([
                    Forms\Components\CheckboxList::make('especialidades')
                        ->relationship('especialidades', 'especialidad')
                        ->required()
                        ->columns(2),
                ]),
        ])
        ->columnSpanFull() // 👈 Esto hará que el Wizard ocupe el 100% del ancho

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
                /*    
                Tables\Columns\ImageColumn::make('persona.foto')
                ->label('Foto')
                ->circular() // Opcional: muestra la imagen en forma circular
                ->defaultImageUrl(url('/images/default-avatar.png')), // Imagen por defecto si no hay foto*/
                    
                Tables\Columns\TextColumn::make('especialidades.especialidad')
                    ->label('Especialidades')
                    ->badge(),

            ])
            ->filters([
                // Filtros opcionales
            ])
            ->actions([

                Tables\Actions\ViewAction::make() // Botón "Ver"
                    ->icon('heroicon-o-eye')
                    ->color('gray'),

                Tables\Actions\EditAction::make() // Botón "Editar"
                    ->icon('heroicon-o-pencil')
                    ->color('primary'),

                Tables\Actions\DeleteAction::make() // Botón "Eliminar"
                    ->icon('heroicon-o-trash')
                    ->color('danger'),
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
            'index' => Pages\ListMedicos::route('/'),
            'create' => Pages\CreateMedico::route('/create'),
            'view' => Pages\ViewMedico::route('/{record}'), //
            'edit' => Pages\EditMedico::route('/{record}/edit'),
        ];
    }

    public static function handleMedicoCreation(array $data): Medico
    {
        DB::beginTransaction();
        
        try {
            // Crear o actualizar persona
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

            // Crear o actualizar médico
            $medico = Medico::updateOrCreate(
                ['persona_id' => $persona->id],
                ['numero_colegiacion' => $data['numero_colegiacion']]
            );

            // Sincronizar especialidades
            if (isset($data['especialidades'])) {
                $medico->especialidades()->sync($data['especialidades']);
            }

            DB::commit();
            
            return $medico;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}