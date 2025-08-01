<?php

namespace App\Filament\Resources\ContabilidadMedica\CargoMedicoResource\Pages\ListCargoMedicos\Widgets;

use App\Models\ContabilidadMedica\CargoMedico;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class CargoMedicosStats extends BaseWidget
{
    protected function getStats(): array
    {
        // Calculamos los totales por estado
        $totalPendiente = CargoMedico::where('estado', 'pendiente')->sum('total');
        $totalParcial = CargoMedico::where('estado', 'parcial')->sum('total');
        $totalPagado = CargoMedico::where('estado', 'pagado')->sum('total');
        
        // Calculamos los conteos por estado
        $conteosPorEstado = CargoMedico::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();
            
        $conteoPendiente = $conteosPorEstado['pendiente'] ?? 0;
        $conteoParcial = $conteosPorEstado['parcial'] ?? 0;
        $conteoPagado = $conteosPorEstado['pagado'] ?? 0;
        
        // Calculamos el total de este mes
        $inicioMes = now()->startOfMonth()->format('Y-m-d');
        $finMes = now()->endOfMonth()->format('Y-m-d');
        
        $totalMes = CargoMedico::whereBetween('created_at', [$inicioMes, $finMes])
            ->sum('total');
        
        return [
            Stat::make('Cargos Pendientes', $conteoPendiente)
                ->description('L. ' . number_format($totalPendiente, 2))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('danger')
                ->chart([7, 4, 6, 8, 5, $conteoPendiente]),
                
            Stat::make('Pagos Parciales', $conteoParcial)
                ->description('L. ' . number_format($totalParcial, 2))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning')
                ->chart([2, 3, 5, 4, 3, $conteoParcial]),
                
            Stat::make('Cargos Pagados', $conteoPagado)
                ->description('L. ' . number_format($totalPagado, 2))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart([3, 5, 7, 8, 6, $conteoPagado]),
                
            Stat::make('Total del Mes', 'L. ' . number_format($totalMes, 2))
                ->description('Cargos de ' . now()->format('M Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
        ];
    }
}
