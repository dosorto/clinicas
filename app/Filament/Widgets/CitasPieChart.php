<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Citas;

class CitasPieChart extends ChartWidget
{
    protected static ?string $heading = 'Estado de las Citas';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    
    protected function getData(): array
    {
        // Obtener las citas agrupadas por estado, filtradas por centro actual
        $currentCentroId = session('current_centro_id');
        
        $query = Citas::query();
        
        // Si no es usuario root, filtrar por centro actual
        if (!auth()->user()?->hasRole('root')) {
            $query->where('centro_id', $currentCentroId);
        }
        
        $pendientes = $query->clone()->where('estado', 'Pendiente')->count();
        $confirmadas = $query->clone()->where('estado', 'Confirmado')->count();
        $canceladas = $query->clone()->where('estado', 'Cancelado')->count();
        $realizadas = $query->clone()->where('estado', 'Realizada')->count();
        
        return [
            'datasets' => [
                [
                    'label' => 'Citas por Estado',
                    'data' => [$pendientes, $confirmadas, $canceladas, $realizadas],
                    'backgroundColor' => [
                        '#fbbf24', // Amarillo para pendientes
                        '#10b981', // Verde para confirmadas
                        '#ef4444', // Rojo para canceladas
                        '#3b82f6', // Azul para realizadas
                    ],
                    'borderColor' => [
                        '#f59e0b',
                        '#059669',
                        '#dc2626',
                        '#2563eb',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Pendientes', 'Confirmadas', 'Canceladas', 'Realizadas'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
    
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Distribución de Citas por Estado',
                ],
            ],
        ];
    }

    public static function canView(): bool
    {
        return true; // Simplificado para debugging
    }
}
