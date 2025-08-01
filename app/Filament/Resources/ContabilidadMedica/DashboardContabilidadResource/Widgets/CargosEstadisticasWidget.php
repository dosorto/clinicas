<?php

namespace App\Filament\Resources\ContabilidadMedica\DashboardContabilidadResource\Widgets;

use App\Models\ContabilidadMedica\CargoMedico;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class CargosEstadisticasWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $cargosPendientes = CargoMedico::where('estado', 'pendiente')->count();
        $cargosParcialesPagados = CargoMedico::where('estado', 'parcial')->count();
        $cargosPagados = CargoMedico::where('estado', 'pagado')->count();
        
        // Obtener total de cargos pendientes
        $montoPendiente = CargoMedico::where('estado', 'pendiente')
            ->sum('total');
            
        // Obtener el total de cargos este mes
        $inicioMes = now()->startOfMonth()->format('Y-m-d');
        $finMes = now()->endOfMonth()->format('Y-m-d');
        
        $cargosMes = CargoMedico::whereBetween('created_at', [$inicioMes, $finMes])
            ->sum('total');
            
        return [
            Stat::make('Cargos Pendientes', $cargosPendientes)
                ->description('Cargos sin pago')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),
                
            Stat::make('Pagos Parciales', $cargosParcialesPagados)
                ->description('Cargos con pago parcial')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),
                
            Stat::make('Cargos Pagados', $cargosPagados)
                ->description('Cargos completamente pagados')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('Monto Pendiente', 'L. ' . number_format($montoPendiente, 2))
                ->description('Total por cobrar')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('danger'),
                
            Stat::make('Cargos Este Mes', 'L. ' . number_format($cargosMes, 2))
                ->description('Generados en el mes actual')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
        ];
    }
}
