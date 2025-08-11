<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Citas;
use Filament\Facades\Filament;

class CitasPieChart extends ChartWidget
{
    protected static ?string $heading = '📊 Distribución de Citas';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = [
        'sm' => 2,
        'md' => 3,
        'lg' => 4,
    ];
    protected static ?string $maxHeight = '300px';
    
    protected function getData(): array
    {
        $currentCentroId = session('current_centro_id');
        $user = Filament::auth()->user();
        
        $query = Citas::query();
        
        if ($user && !$user->hasRole('root')) {
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
                    'data' => [$pendientes, $confirmadas, $realizadas, $canceladas],
                    'backgroundColor' => [
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                    ],
                    'borderColor' => [
                        'rgb(245, 158, 11)',
                        'rgb(22, 163, 74)',
                        'rgb(37, 99, 235)',
                        'rgb(220, 38, 38)',
                    ],
                    'borderWidth' => 3,
                    'hoverOffset' => 10,
                ],
            ],
            'labels' => [
                "⏳ Pendientes ({$pendientes})",
                "✅ Confirmadas ({$confirmadas})",
                "✨ Realizadas ({$realizadas})",
                "❌ Canceladas ({$canceladas})"
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
                        'padding' => 20,
                        'font' => [
                            'size' => 12,
                            'weight' => 'bold'
                        ]
                    ]
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'titleColor' => 'white',
                    'bodyColor' => 'white',
                    'borderColor' => 'rgba(255, 255, 255, 0.2)',
                    'borderWidth' => 1
                ]
            ],
            'cutout' => '60%',
            'animation' => [
                'animateRotate' => true,
                'animateScale' => true
            ]
        ];
    }
}