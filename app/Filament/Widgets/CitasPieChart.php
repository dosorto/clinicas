<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Citas;
use Filament\Facades\Filament;

class CitasPieChart extends ChartWidget
{
    protected static ?string $heading = '📊 Estado de Citas';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;
    protected static ?string $maxHeight = '250px';
    
    protected function getData(): array
    {
        $currentCentroId = session('current_centro_id');
        $user = Filament::auth()->user();
        
        $query = Citas::query();
        
        // Aplicar filtros según el usuario
        if (!$user || !$user->hasRole('root')) {
            if ($currentCentroId) {
                $query->where('centro_id', $currentCentroId);
            }
        }
        
        // Solo citas de hoy para simplificar
        $query->whereDate('fecha', today());
        
        $pendientes = $query->clone()->where('estado', 'Pendiente')->count();
        $confirmadas = $query->clone()->where('estado', 'Confirmado')->count();
        $realizadas = $query->clone()->where('estado', 'Realizada')->count();
        $canceladas = $query->clone()->where('estado', 'Cancelado')->count();
        
        return [
            'datasets' => [
                [
                    'label' => 'Citas de Hoy',
                    'data' => [$pendientes, $confirmadas, $realizadas, $canceladas],
                    'backgroundColor' => [
                        'rgba(251, 191, 36, 0.8)',  // Amarillo para pendientes
                        'rgba(34, 197, 94, 0.8)',   // Verde para confirmadas  
                        'rgba(59, 130, 246, 0.8)',  // Azul para realizadas
                        'rgba(239, 68, 68, 0.8)',   // Rojo para canceladas
                    ],
                    'borderWidth' => 2,
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => [
                "Pendientes ({$pendientes})",
                "Confirmadas ({$confirmadas})", 
                "Realizadas ({$realizadas})",
                "Canceladas ({$canceladas})"
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
    
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 15,
                        'font' => [
                            'size' => 11
                        ]
                    ]
                ]
            ],
            'cutout' => '65%',
        ];
    }
}