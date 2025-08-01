<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NominaSimpleResource\Pages;
use App\Models\Medico;
use App\Models\ContabilidadMedica\ContratoMedico;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class NominaSimpleResource extends Resource
{
    protected static ?string $model = ContratoMedico::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    
    // Ocultar este recurso ya que tenemos la versión mejorada
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    protected static ?string $navigationGroup = 'Contabilidad Médica';
    protected static ?string $navigationLabel = 'Nómina Sencilla';
    protected static ?string $modelLabel = 'Nómina';
    protected static ?string $pluralModelLabel = 'Nóminas';
    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                ContratoMedico::query()
                    ->with(['medico.persona', 'centro'])
                    ->where('activo', 'SI')
            )
            ->columns([
                Tables\Columns\TextColumn::make('medico_nombre')
                    ->label('Doctor/a')
                    ->getStateUsing(function (ContratoMedico $record) {
                        if ($record->medico && $record->medico->persona) {
                            return 'Dr. ' . $record->medico->persona->nombre_completo;
                        }
                        return 'Sin médico asignado';
                    })
                    ->searchable(false)
                    ->sortable(false)
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('centro.nombre_centro')
                    ->label('Centro Médico')
                    ->searchable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('salario_mensual')
                    ->label('Salario Mensual')
                    ->money('HNL')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('porcentaje_servicio')
                    ->label('% Servicios')
                    ->suffix('%')
                    ->alignCenter()
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Desde')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                // Sin filtros para mantenerlo simple
            ])
            ->actions([
                Tables\Actions\Action::make('generar_nomina_mes')
                    ->label('Nómina del Mes')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->action(function (ContratoMedico $record) {
                        $fechaInicio = Carbon::now()->startOfMonth();
                        $fechaFin = Carbon::now()->endOfMonth();
                        
                        $url = route('nomina.generar.pdf', [
                            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                            'fecha_fin' => $fechaFin->format('Y-m-d'),
                            'medico_id' => $record->medico_id,
                            'incluir_pagados' => true,
                            'incluir_pendientes' => true,
                        ]);
                        
                        Notification::make()
                            ->title('Generando nómina...')
                            ->success()
                            ->send();
                            
                        return redirect($url);
                    }),

                Tables\Actions\Action::make('generar_nomina_personalizada')
                    ->label('Nómina Personalizada')
                    ->icon('heroicon-o-calendar')
                    ->color('primary')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('fecha_inicio')
                                    ->label('Desde')
                                    ->required()
                                    ->default(Carbon::now()->startOfMonth()),
                                
                                Forms\Components\DatePicker::make('fecha_fin')
                                    ->label('Hasta')
                                    ->required()
                                    ->default(Carbon::now())
                                    ->afterOrEqual('fecha_inicio'),
                            ]),
                    ])
                    ->action(function (ContratoMedico $record, array $data) {
                        $url = route('nomina.generar.pdf', [
                            'fecha_inicio' => $data['fecha_inicio'],
                            'fecha_fin' => $data['fecha_fin'],
                            'medico_id' => $record->medico_id,
                            'incluir_pagados' => true,
                            'incluir_pendientes' => true,
                        ]);
                        
                        Notification::make()
                            ->title('Generando nómina personalizada...')
                            ->success()
                            ->send();
                            
                        return redirect($url);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('generar_nomina_todos')
                    ->label('Nómina Todos')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('warning')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('fecha_inicio')
                                    ->label('Desde')
                                    ->required()
                                    ->default(Carbon::now()->startOfMonth()),
                                
                                Forms\Components\DatePicker::make('fecha_fin')
                                    ->label('Hasta')
                                    ->required()
                                    ->default(Carbon::now())
                                    ->afterOrEqual('fecha_inicio'),
                            ]),
                    ])
                    ->action(function (array $data) {
                        $url = route('nomina.generar.pdf', [
                            'fecha_inicio' => $data['fecha_inicio'],
                            'fecha_fin' => $data['fecha_fin'],
                            'incluir_pagados' => true,
                            'incluir_pendientes' => true,
                        ]);
                        
                        Notification::make()
                            ->title('Generando nómina para todos los médicos...')
                            ->success()
                            ->send();
                            
                        return redirect($url);
                    }),
            ])
            ->emptyStateHeading('No hay contratos activos')
            ->emptyStateDescription('Registre contratos médicos para generar nóminas')
            ->defaultSort('fecha_inicio', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageNominaSimples::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
