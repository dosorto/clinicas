<?php

namespace App\Filament\Resources\ContabilidadMedica\DashboardContabilidadResource\Widgets;

use App\Models\ContabilidadMedica\LiquidacionHonorario;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LiquidacionesEstadisticasWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $liquidacionesPendientes = LiquidacionHonorario::where('estado', 'pendiente')->count();
        $liquidacionesParcialesPagadas = LiquidacionHonorario::where('estado', 'parcial')->count();
        $liquidacionesPagadas = LiquidacionHonorario::where('estado', 'pagado')->count();
        
        // Obtener total de liquidaciones pendientes
        $montoPendiente = LiquidacionHonorario::where('estado', 'pendiente')
            ->sum('monto_total');
            
        // Obtener el total de liquidaciones este mes
        $inicioMes = now()->startOfMonth()->format('Y-m-d');
        $finMes = now()->endOfMonth()->format('Y-m-d');
        
        $liquidacionesMes = LiquidacionHonorario::whereBetween('created_at', [$inicioMes, $finMes])
            ->sum('monto_total');
            
        return [
            Stat::make('Liquidaciones Pendientes', $liquidacionesPendientes)
                ->description('Liquidaciones sin pago')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),
                
            Stat::make('Pagos Parciales', $liquidacionesParcialesPagadas)
                ->description('Liquidaciones con pago parcial')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),
                
            Stat::make('Liquidaciones Pagadas', $liquidacionesPagadas)
                ->description('Liquidaciones completamente pagadas')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('Monto Pendiente', 'L. ' . number_format($montoPendiente, 2))
                ->description('Total por pagar')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('danger'),
                
            Stat::make('Liquidaciones Este Mes', 'L. ' . number_format($liquidacionesMes, 2))
                ->description('Generadas en el mes actual')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
        ];
    }
}
