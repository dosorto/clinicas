<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NominaResource\Pages;
use App\Models\ContabilidadMedica\ContratoMedico;
use App\Models\Medico;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NominaResource extends Resource
{
    protected static ?string $model = Medico::class; // Usamos Medico como base, pero realmente no usaremos operaciones CRUD estándar

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Contabilidad Médica';
    protected static ?string $navigationLabel = 'Nómina Avanzada (Oculta)';
    protected static ?string $modelLabel = 'Nómina';
    protected static ?string $pluralModelLabel = 'Nóminas';
    protected static ?int $navigationSort = 99;
    protected static bool $shouldRegisterNavigation = false; // Ocultar del menú

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Este formulario es solo para generación de nómina, no para CRUD
                Forms\Components\Section::make('Generar nómina')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\DatePicker::make('fecha_inicio')
                                    ->label('Fecha de inicio')
                                    ->required()
                                    ->default(Carbon::now()->startOfMonth())
                                    ->maxDate(Carbon::now()),
                                
                                Forms\Components\DatePicker::make('fecha_fin')
                                    ->label('Fecha de fin')
                                    ->required()
                                    ->default(Carbon::now())
                                    ->maxDate(Carbon::now())
                                    ->afterOrEqual('fecha_inicio'),
                            ]),
                            
                        Forms\Components\Select::make('medico_id')
                            ->label('Médico (opcional)')
                            ->options(function() {
                                return Medico::all()->pluck('nombre_completo', 'id');
                            })
                            ->searchable()
                            ->placeholder('Todos los médicos'),
                            
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Checkbox::make('incluir_pagados')
                                    ->label('Incluir pagos realizados')
                                    ->default(true),
                                    
                                Forms\Components\Checkbox::make('incluir_pendientes')
                                    ->label('Incluir liquidaciones pendientes')
                                    ->default(false),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Medico::query()->with(['persona', 'especialidades', 'centro'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('nombre_medico')
                    ->label('Médico')
                    ->searchable(false)
                    ->sortable(false)
                    ->getStateUsing(function (Medico $record) {
                        if ($record->persona) {
                            return $record->persona->nombre . ' ' . $record->persona->apellido;
                        }
                        return 'Médico #' . $record->id;
                    }),
                    
                Tables\Columns\TextColumn::make('especialidades')
                    ->label('Especialidad')
                    ->searchable(false)
                    ->sortable(false)
                    ->getStateUsing(function (Medico $record) {
                        return $record->especialidades->pluck('nombre')->join(', ') ?: 'No especificada';
                    }),
                    
                Tables\Columns\TextColumn::make('contratos_count')
                    ->counts('contratos')
                    ->label('Contratos'),
                    
                Tables\Columns\TextColumn::make('cargos_count')
                    ->counts('cargos')
                    ->label('Cargos médicos'),
                    
                Tables\Columns\TextColumn::make('liquidaciones_count')
                    ->counts('liquidaciones')
                    ->label('Liquidaciones'),
                    
                Tables\Columns\TextColumn::make('pagos_count')
                    ->counts('pagos')
                    ->label('Pagos'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('generar_nomina')
                    ->label('Generar nómina')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->form([
                        Forms\Components\DatePicker::make('fecha_inicio')
                            ->label('Fecha de inicio')
                            ->required()
                            ->default(Carbon::now()->startOfMonth())
                            ->maxDate(Carbon::now()),
                        
                        Forms\Components\DatePicker::make('fecha_fin')
                            ->label('Fecha de fin')
                            ->required()
                            ->default(Carbon::now())
                            ->maxDate(Carbon::now())
                            ->afterOrEqual('fecha_inicio'),
                            
                        Forms\Components\Checkbox::make('incluir_pagados')
                            ->label('Incluir pagos realizados')
                            ->default(true),
                            
                        Forms\Components\Checkbox::make('incluir_pendientes')
                            ->label('Incluir liquidaciones pendientes')
                            ->default(false),
                    ])
                    ->action(function (Medico $record, array $data) {
                        // Redirigir a la URL de generación de PDF con los parámetros necesarios
                        $queryParams = http_build_query([
                            'fecha_inicio' => $data['fecha_inicio'],
                            'fecha_fin' => $data['fecha_fin'],
                            'medico_id' => $record->id,
                            'incluir_pagados' => $data['incluir_pagados'] ?? true,
                            'incluir_pendientes' => $data['incluir_pendientes'] ?? false,
                        ]);
                        
                        return redirect()->to(route('nomina.generar.pdf') . '?' . $queryParams);
                    }),
            ])
            ->bulkActions([
                // Sin acciones masivas
            ])
            ->emptyStateHeading('No hay médicos registrados')
            ->emptyStateDescription('Registre médicos para generar nóminas');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageNominas::route('/'),
        ];
    }
}
