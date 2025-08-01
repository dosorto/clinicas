<?php

namespace App\Filament\Resources\ContabilidadMedica\CargoMedicoResource\Pages;

use App\Filament\Resources\ContabilidadMedica\CargoMedicoResource;
use App\Models\ContabilidadMedica\CargoMedico;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\IconSize;
use Illuminate\Support\Facades\DB;

class ListCargoMedicos extends ListRecords
{
    protected static string $resource = CargoMedicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Crear Cargo')
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
            // Stat card para cargos pendientes
            ListCargoMedicos\Widgets\CargoMedicosStats::class,
        ];
    }
}
