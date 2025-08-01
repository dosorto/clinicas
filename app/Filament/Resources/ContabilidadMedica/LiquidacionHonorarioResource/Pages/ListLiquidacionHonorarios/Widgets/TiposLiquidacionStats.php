<?php

namespace App\Filament\Resources\ContabilidadMedica\LiquidacionHonorarioResource\Pages\ListLiquidacionHonorarios\Widgets;

use App\Models\ContabilidadMedica\LiquidacionHonorario;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class TiposLiquidacionStats extends BaseWidget
{
    protected function getStats(): array
    {
        // Contar por tipo de liquidación
        $conteoPorTipo = LiquidacionHonorario::select('tipo_liquidacion', DB::raw('count(*) as total'))
            ->groupBy('tipo_liquidacion')
            ->pluck('total', 'tipo_liquidacion')
            ->toArray();
            
        $liquidacionesServicios = $conteoPorTipo['servicios'] ?? 0;
        $liquidacionesHonorarios = $conteoPorTipo['honorarios'] ?? 0;
        $liquidacionesMixtas = $conteoPorTipo['mixto'] ?? 0;
            
        return [
            Stat::make('Liquidaciones por Servicios', $liquidacionesServicios)
                ->color('primary')
                ->icon('heroicon-m-clipboard-document-check'),
                
            Stat::make('Liquidaciones por Honorarios', $liquidacionesHonorarios)
                ->color('primary')
                ->icon('heroicon-m-currency-dollar'),
                
            Stat::make('Liquidaciones Mixtas', $liquidacionesMixtas)
                ->color('primary')
                ->icon('heroicon-m-clipboard-document-list'),
        ];
    }
}
