<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HistorialNominaResource\Pages;
use App\Models\ContabilidadMedica\ContratoMedico;
use App\Models\ContabilidadMedica\LiquidacionHonorario;
use App\Models\ContabilidadMedica\PagoHonorario;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;

class HistorialNominaResource extends Resource
{
    protected static ?string $model = LiquidacionHonorario::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Contabilidad Médica';
    protected static ?string $navigationLabel = 'Historial de Nóminas';
    protected static ?string $modelLabel = 'Historial de Nómina';
    protected static ?string $pluralModelLabel = 'Historial de Nóminas';
    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                LiquidacionHonorario::query()
                    ->with(['medico.persona', 'contratoMedico.centro'])
                    ->whereHas('contratoMedico', function ($query) {
                        $centroUsuario = Filament::auth()->user()?->centro_id;
                        if ($centroUsuario) {
                            $query->where('centro_id', $centroUsuario);
                        }
                    })
                    ->orderBy('periodo_inicio', 'desc')
            )
            ->columns([
                TextColumn::make('medico_nombre')
                    ->label('Doctor/a')
                    ->getStateUsing(function (LiquidacionHonorario $record) {
                        if ($record->medico && $record->medico->persona) {
                            return 'Dr. ' . $record->medico->persona->nombre_completo;
                        }
                        return 'Médico #' . $record->medico_id;
                    })
                    ->searchable(false)
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('centro_medico')
                    ->label('Centro Médico')
                    ->getStateUsing(function (LiquidacionHonorario $record) {
                        return $record->contratoMedico?->centro?->nombre_centro ?? 'Sin centro';
                    })
                    ->badge()
                    ->color('success'),

                TextColumn::make('periodo')
                    ->label('Período')
                    ->getStateUsing(function (LiquidacionHonorario $record) {
                        $inicio = Carbon::parse($record->periodo_inicio)->format('d/m/Y');
                        $fin = Carbon::parse($record->periodo_fin)->format('d/m/Y');
                        return $inicio . ' - ' . $fin;
                    })
                    ->badge()
                    ->color('info'),

                TextColumn::make('servicios_brutos')
                    ->label('Servicios Brutos')
                    ->money('HNL')
                    ->alignRight(),

                TextColumn::make('deducciones')
                    ->label('Deducciones')
                    ->money('HNL')
                    ->alignRight()
                    ->color('warning'),

                TextColumn::make('monto_total')
                    ->label('Total Neto')
                    ->money('HNL')
                    ->alignRight()
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PAGADO' => 'success',
                        'PENDIENTE' => 'warning',
                        'PROCESANDO' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('fecha_liquidacion')
                    ->label('Fecha Liquidación')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'PAGADO' => 'Pagado',
                        'PENDIENTE' => 'Pendiente',
                        'PROCESANDO' => 'Procesando',
                    ]),
                
                Tables\Filters\Filter::make('periodo')
                    ->form([
                        Forms\Components\DatePicker::make('desde')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('hasta')
                            ->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['desde'], fn ($q) => $q->where('periodo_inicio', '>=', $data['desde']))
                            ->when($data['hasta'], fn ($q) => $q->where('periodo_fin', '<=', $data['hasta']));
                    })
            ])
            ->actions([
                Tables\Actions\Action::make('ver_detalle')
                    ->label('Ver Detalle')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalContent(function (LiquidacionHonorario $record) {
                        $medico = $record->medico;
                        $contrato = $record->contratoMedico;
                        
                        return view('filament.modals.detalle-liquidacion', [
                            'liquidacion' => $record,
                            'medico' => $medico,
                            'contrato' => $contrato,
                        ]);
                    })
                    ->modalHeading(fn (LiquidacionHonorario $record) => 'Detalle de Nómina - ' . 
                        ($record->medico?->persona?->nombre_completo ?? 'Médico'))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),

                Tables\Actions\Action::make('generar_comprobante')
                    ->label('Comprobante')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->action(function (LiquidacionHonorario $record) {
                        $fechaInicio = $record->periodo_inicio;
                        $fechaFin = $record->periodo_fin;
                        
                        $url = route('nomina.generar.pdf', [
                            'fecha_inicio' => $fechaInicio,
                            'fecha_fin' => $fechaFin,
                            'medico_id' => $record->medico_id,
                            'incluir_pagados' => true,
                            'incluir_pendientes' => false,
                        ]);
                        
                        return redirect($url);
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('crear_nueva_nomina')
                    ->label('Crear Nueva Nómina')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->url(fn () => NominaMedicaSimpleResource::getUrl('create'))
                    ->button(),
            ])
            ->emptyStateHeading('No hay liquidaciones registradas')
            ->emptyStateDescription('Las liquidaciones aparecerán aquí una vez que se procesen las nóminas')
            ->emptyStateIcon('heroicon-o-banknotes')
            ->defaultSort('periodo_inicio', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHistorialNominas::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Se crean desde el NominaMedicaSimpleResource
    }

    public static function canEdit($record): bool
    {
        return false; // Solo lectura
    }

    public static function canDelete($record): bool
    {
        return false; // Solo lectura
    }
}
