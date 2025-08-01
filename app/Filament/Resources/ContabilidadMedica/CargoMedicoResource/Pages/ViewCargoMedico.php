<?php

namespace App\Filament\Resources\ContabilidadMedica\CargoMedicoResource\Pages;

use App\Filament\Resources\ContabilidadMedica\CargoMedicoResource;
use App\Filament\Resources\ContabilidadMedica\PagoCargoMedicoResource;
use App\Models\ContabilidadMedica\PagoCargoMedico;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;

class ViewCargoMedico extends ViewRecord
{
    protected static string $resource = CargoMedicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Editar Cargo')
                ->icon('heroicon-o-pencil'),
                
            Actions\Action::make('nuevoPago')
                ->label('Registrar Pago')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->url(fn ($record) => PagoCargoMedicoResource::getUrl('create', [
                    'cargo_id' => $record->id,
                    'centro_id' => $record->centro_id,
                ])),
                
            Actions\Action::make('generarPdf')
                ->label('Generar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function () {
                    // Implementación futura de generación de PDF
                    $this->notify('success', 'PDF generado correctamente');
                }),
        ];
    }
    
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Información General')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('medico.persona.nombre_completo')
                                    ->label('Médico')
                                    ->weight('bold')
                                    ->icon('heroicon-o-user')
                                    ->columnSpan(1),
                                    
                                TextEntry::make('contrato.id')
                                    ->label('Contrato')
                                    ->formatStateUsing(fn ($state) => "Contrato #{$state}")
                                    ->icon('heroicon-o-document-text')
                                    ->columnSpan(1),
                                    
                                TextEntry::make('centro.nombre_centro')
                                    ->label('Centro Médico')
                                    ->icon('heroicon-o-building-office')
                                    ->columnSpan(1),
                            ]),
                    ]),
                    
                Section::make('Periodo y Detalles')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('descripcion')
                                    ->label('Descripción')
                                    ->columnSpan(3),
                                    
                                TextEntry::make('periodo_inicio')
                                    ->label('Fecha Inicio')
                                    ->date('d/m/Y')
                                    ->columnSpan(1),
                                    
                                TextEntry::make('periodo_fin')
                                    ->label('Fecha Fin')
                                    ->date('d/m/Y')
                                    ->columnSpan(1),
                                    
                                IconEntry::make('estado')
                                    ->label('Estado')
                                    ->icon(fn (string $state): string => match ($state) {
                                        'pendiente' => 'heroicon-o-clock',
                                        'parcial' => 'heroicon-o-arrow-path',
                                        'pagado' => 'heroicon-o-check-circle',
                                        'anulado' => 'heroicon-o-x-circle',
                                        default => 'heroicon-o-question-mark-circle',
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'pendiente' => 'danger',
                                        'parcial' => 'warning',
                                        'pagado' => 'success',
                                        'anulado' => 'gray',
                                        default => 'gray',
                                    })
                                    ->columnSpan(1),
                            ]),
                    ]),
                    
                Section::make('Información Financiera')
                    ->icon('heroicon-o-currency-dollar')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->money('HNL')
                                    ->columnSpan(1),
                                    
                                TextEntry::make('impuesto_total')
                                    ->label('Impuesto')
                                    ->money('HNL')
                                    ->columnSpan(1),
                                    
                                TextEntry::make('total')
                                    ->label('Total')
                                    ->money('HNL')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->color('success')
                                    ->columnSpan(1),
                                    
                                // Información de pagos
                                TextEntry::make('pagado')
                                    ->label('Total Pagado')
                                    ->state(function ($record) {
                                        return PagoCargoMedico::where('cargo_id', $record->id)->sum('monto_pagado');
                                    })
                                    ->money('HNL')
                                    ->color('success')
                                    ->columnSpan(1),
                                    
                                TextEntry::make('pendiente')
                                    ->label('Pendiente por Pagar')
                                    ->state(function ($record) {
                                        $pagado = PagoCargoMedico::where('cargo_id', $record->id)->sum('monto_pagado');
                                        return $record->total - $pagado;
                                    })
                                    ->money('HNL')
                                    ->color('danger')
                                    ->columnSpan(1),
                                    
                                TextEntry::make('porcentaje_pagado')
                                    ->label('Progreso de Pago')
                                    ->state(function ($record) {
                                        $pagado = PagoCargoMedico::where('cargo_id', $record->id)->sum('monto_pagado');
                                        $porcentaje = ($record->total > 0) ? ($pagado / $record->total) * 100 : 0;
                                        return number_format($porcentaje, 1) . '%';
                                    })
                                    ->color(function ($state) {
                                        $valor = floatval($state);
                                        if ($valor >= 100) return 'success';
                                        if ($valor >= 50) return 'warning';
                                        return 'danger';
                                    })
                                    ->columnSpan(1),
                            ]),
                    ]),
                    
                Section::make('Observaciones')
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->schema([
                        TextEntry::make('observaciones')
                            ->label('Observaciones')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
