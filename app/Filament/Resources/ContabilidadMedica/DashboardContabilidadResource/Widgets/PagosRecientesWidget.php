<?php

namespace App\Filament\Resources\ContabilidadMedica\DashboardContabilidadResource\Widgets;

use App\Models\ContabilidadMedica\PagoCargoMedico;
use App\Models\ContabilidadMedica\PagoHonorario;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PagosRecientesWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    
    protected function getTableQuery(): Builder
    {
        // Como no podemos combinar directamente dos modelos diferentes en una consulta,
        // vamos a usar un enfoque alternativo mostrando solo uno de los modelos.
        // Luego podemos reemplazar este widget con una implementación personalizada.
        
        return PagoCargoMedico::query()
            ->with(['cargo.medico.persona', 'centro'])
            ->orderBy('fecha_pago', 'desc')
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('cargo.medico.persona.nombre_completo')
                ->label('Médico')
                ->searchable(),
                
            Tables\Columns\TextColumn::make('fecha_pago')
                ->label('Fecha de Pago')
                ->date(),
                
            Tables\Columns\TextColumn::make('monto_pagado')
                ->label('Monto')
                ->money('HNL'),
                
            Tables\Columns\TextColumn::make('metodo_pago')
                ->label('Método de Pago')
                ->formatStateUsing(fn (string $state): string => ucfirst($state))
                ->badge(),
        ];
    }
    
    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('ver')
                ->label('Ver')
                ->url(fn (PagoCargoMedico $record): string => '/admin/contabilidad-medica/pago-cargo-medicos/' . $record->id)
                ->icon('heroicon-o-eye'),
        ];
    }
    
    protected function getTableHeading(): ?string
    {
        return 'Pagos Recientes';
    }
    
    protected function getTableDescription(): ?string
    {
        return 'Últimos pagos de cargos médicos';
    }
}
