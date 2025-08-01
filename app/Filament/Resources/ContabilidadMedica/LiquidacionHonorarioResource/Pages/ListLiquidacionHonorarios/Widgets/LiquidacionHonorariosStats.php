<?php

namespace App\Filament\Resources\ContabilidadMedica\LiquidacionHonorarioResource\Pages\ListLiquidacionHonorarios\Widgets;

use App\Models\ContabilidadMedica\LiquidacionHonorario;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class LiquidacionHonorariosStats extends BaseWidget
{
    protected function getStats(): array
    {
        // Calculamos los totales por estado
        $totalPendiente = LiquidacionHonorario::where('estado', 'pendiente')->sum('monto_total');
        $totalParcial = LiquidacionHonorario::where('estado', 'parcial')->sum('monto_total');
        $totalPagado = LiquidacionHonorario::where('estado', 'pagado')->sum('monto_total');
        
        // Calculamos los conteos por estado
        $conteosPorEstado = LiquidacionHonorario::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();
            
        $conteoPendiente = $conteosPorEstado['pendiente'] ?? 0;
        $conteoParcial = $conteosPorEstado['parcial'] ?? 0;
        $conteoPagado = $conteosPorEstado['pagado'] ?? 0;
        
        // Calculamos el total de este mes
        $inicioMes = now()->startOfMonth()->format('Y-m-d');
        $finMes = now()->endOfMonth()->format('Y-m-d');
        
        $totalMes = LiquidacionHonorario::whereBetween('created_at', [$inicioMes, $finMes])
            ->sum('monto_total');
            
        return [
            Stat::make('Liquidaciones Pendientes', $conteoPendiente)
                ->description('L. ' . number_format($totalPendiente, 2))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('danger')
                ->chart([5, 3, 7, 5, 4, $conteoPendiente]),
                
            Stat::make('Pagos Parciales', $conteoParcial)
                ->description('L. ' . number_format($totalParcial, 2))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning')
                ->chart([2, 4, 3, 5, 4, $conteoParcial]),
                
            Stat::make('Liquidaciones Pagadas', $conteoPagado)
                ->description('L. ' . number_format($totalPagado, 2))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart([4, 6, 8, 7, 5, $conteoPagado]),
                
            Stat::make('Total del Mes', 'L. ' . number_format($totalMes, 2))
                ->description('Liquidaciones de ' . now()->format('M Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
        ];
    }
}
