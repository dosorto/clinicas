<?php

namespace App\Filament\Resources\ContabilidadMedica\DashboardContabilidadResource\Widgets;

use App\Models\ContabilidadMedica\PagoCargoMedico;
use App\Models\ContabilidadMedica\PagoHonorario;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GraficoIngresosMensualesWidget extends ChartWidget
{
    protected static ?string $heading = 'Ingresos Mensuales';
    protected static ?string $subheading = 'Últimos 6 meses';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Obtenemos los últimos 6 meses
        $meses = collect();
        for ($i = 5; $i >= 0; $i--) {
            $meses->push(now()->subMonths($i));
        }

        // Formateamos los meses para las etiquetas
        $labels = $meses->map(fn ($mes) => $mes->format('M Y'))->toArray();

        // Obtenemos los pagos de cargos por mes
        $pagosCargos = collect();
        foreach ($meses as $mes) {
            $inicioMes = $mes->copy()->startOfMonth();
            $finMes = $mes->copy()->endOfMonth();

            $totalMes = PagoCargoMedico::whereBetween('fecha_pago', [$inicioMes, $finMes])
                ->sum('monto_pagado');

            $pagosCargos->push($totalMes);
        }

        // Obtenemos los pagos de honorarios por mes
        $pagosHonorarios = collect();
        foreach ($meses as $mes) {
            $inicioMes = $mes->copy()->startOfMonth();
            $finMes = $mes->copy()->endOfMonth();

            $totalMes = PagoHonorario::whereBetween('fecha_pago', [$inicioMes, $finMes])
                ->sum('monto_pagado');

            $pagosHonorarios->push($totalMes);
        }

        // Total combinado
        $total = $pagosCargos->zip($pagosHonorarios)->map(fn ($valores) => $valores[0] + $valores[1])->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Pagos de Cargos',
                    'data' => $pagosCargos->toArray(),
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#36A2EB',
                ],
                [
                    'label' => 'Pagos de Honorarios',
                    'data' => $pagosHonorarios->toArray(),
                    'backgroundColor' => '#FF6384',
                    'borderColor' => '#FF6384',
                ],
                [
                    'label' => 'Total',
                    'data' => $total,
                    'backgroundColor' => '#4BC0C0',
                    'borderColor' => '#4BC0C0',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
