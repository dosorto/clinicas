<?php

namespace App\Filament\Widgets;

use App\Models\ContabilidadMedica\LiquidacionHonorario;
use App\Models\ContabilidadMedica\PagoHonorario;
use App\Models\Medico;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class NominaStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15m';
    
    protected function getStats(): array
    {
        // Obtener el primer y último día del mes actual
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();
        
        // Obtener el mes anterior
        $inicioMesAnterior = Carbon::now()->subMonth()->startOfMonth();
        $finMesAnterior = Carbon::now()->subMonth()->endOfMonth();
        
        // Estadísticas del mes actual
        $liquidacionesMes = LiquidacionHonorario::whereBetween('created_at', [$inicioMes, $finMes])->sum('monto_total');
        $pagosMes = PagoHonorario::whereBetween('fecha_pago', [$inicioMes, $finMes])->sum('monto_pagado');
        $retencionesMes = PagoHonorario::whereBetween('fecha_pago', [$inicioMes, $finMes])->sum('retencion_isr_monto');
        
        // Estadísticas del mes anterior
        $liquidacionesMesAnterior = LiquidacionHonorario::whereBetween('created_at', [$inicioMesAnterior, $finMesAnterior])->sum('monto_total');
        $pagosMesAnterior = PagoHonorario::whereBetween('fecha_pago', [$inicioMesAnterior, $finMesAnterior])->sum('monto_pagado');
        
        // Calcular diferencias
        $difLiquidaciones = $liquidacionesMesAnterior > 0 
            ? (($liquidacionesMes - $liquidacionesMesAnterior) / $liquidacionesMesAnterior) * 100 
            : 0;
        
        $difPagos = $pagosMesAnterior > 0 
            ? (($pagosMes - $pagosMesAnterior) / $pagosMesAnterior) * 100 
            : 0;
        
        // Número de médicos con pagos este mes (usando liquidaciones porque los pagos pueden no tener medico_id directamente)
        $medicosConPagos = LiquidacionHonorario::whereBetween('created_at', [$inicioMes, $finMes])
            ->distinct('medico_id')
            ->count('medico_id');
        
        return [
            Stat::make('Liquidaciones (Mes Actual)', 'L. ' . number_format($liquidacionesMes, 2))
                ->description($difLiquidaciones >= 0 
                    ? number_format(abs($difLiquidaciones), 1) . '% de aumento respecto al mes anterior' 
                    : number_format(abs($difLiquidaciones), 1) . '% de disminución respecto al mes anterior')
                ->descriptionIcon($difLiquidaciones >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($difLiquidaciones >= 0 ? 'success' : 'danger')
                ->chart([
                    $liquidacionesMesAnterior / 1000, 
                    $liquidacionesMes / 1000
                ]),
                
            Stat::make('Pagos Realizados (Mes Actual)', 'L. ' . number_format($pagosMes, 2))
                ->description($difPagos >= 0 
                    ? number_format(abs($difPagos), 1) . '% de aumento respecto al mes anterior' 
                    : number_format(abs($difPagos), 1) . '% de disminución respecto al mes anterior')
                ->descriptionIcon($difPagos >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($difPagos >= 0 ? 'success' : 'danger')
                ->chart([
                    $pagosMesAnterior / 1000, 
                    $pagosMes / 1000
                ]),
                
            Stat::make('Retenciones (Mes Actual)', 'L. ' . number_format($retencionesMes, 2))
                ->description('Médicos con pagos: ' . $medicosConPagos . ' de ' . Medico::count())
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),
        ];
    }
}
