<?php

namespace App\Filament\Resources\ContabilidadMedica\LiquidacionHonorarioResource\Pages;

use App\Filament\Resources\ContabilidadMedica\LiquidacionHonorarioResource;
use App\Models\ContabilidadMedica\LiquidacionHonorario;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\IconSize;
use Illuminate\Support\Facades\DB;

class ListLiquidacionHonorarios extends ListRecords
{
    protected static string $resource = LiquidacionHonorarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Crear Liquidación')
                ->icon('heroicon-o-plus'),
            
            Actions\Action::make('export')
                ->label('Exportar')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    // Aquí podríamos implementar la exportación a Excel/PDF
                    // Por ahora solo mostramos un mensaje
                    $this->notify('success', 'Exportación iniciada');
                }),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            // Stat card para liquidaciones pendientes
            ListLiquidacionHonorarios\Widgets\LiquidacionHonorariosStats::class,
            
            // Stat card para tipos de liquidación
            ListLiquidacionHonorarios\Widgets\TiposLiquidacionStats::class,
        ];
    }
}
