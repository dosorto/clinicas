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
use Filament\Actions\Action;
use Filament\Tables\Actions\ActionGroup;

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

                     Forms\Components\TextInput::make('dni')
                        ->label('DNI')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Ingrese su DNI')
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
                                    // Guardar datos en session o en propiedad Livewire si usas Livewire Component
                                    session(['dni' => $value]);
                                };
                            },
                        ]),




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
                    Forms\Components\TextInput::make('numero_colegiacion')
                        ->label('Número de Colegiación')
                        ->required()
                        ->maxLength(20)
                        ->placeholder('Ingrese su número de colegiación')
                        ->unique('medicos', 'numero_colegiacion', ignoreRecord: true),
                ]) ->columns(2),
                
            Wizard\Step::make('Especialidades')
                ->schema([
                    Forms\Components\CheckboxList::make('especialidades')
                        ->relationship('especialidades', 'especialidad')
                        ->required()
                        
                        ->columns(2),
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

        protected function getCreateFormAction(): Action
    {
        return Action::make('create')
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

public static function handleMedicoCreation(array $data): Medico
{
    DB::beginTransaction();
    
    try {
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

        $medico = Medico::updateOrCreate(
            ['persona_id' => $persona->id],
            [
                'numero_colegiacion' => $data['numero_colegiacion'],
                'horario_entrada' => $data['horario_entrada'],
                'horario_salida' => $data['horario_salida']
            ]
        );

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






