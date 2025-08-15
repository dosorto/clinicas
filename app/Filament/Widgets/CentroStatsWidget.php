<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Tenant;
use App\Models\Centros_Medico;
use Filament\Support\Colors\Color;

class CentroStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
    protected string|int|array $columnSpan = 'full';
    protected static ?int $sort = 1;

    public function getGridColumns(): array
    {
        return [
            'default' => 2,
            'sm' => 2,
            'md' => 3,
            'lg' => 4,
        ];
    }

    protected function getStats(): array
    {
        $currentCentroId = session('current_centro_id');
        $user = auth()->user();
        
        if (!$currentCentroId && $user && !$user->hasRole('root')) {
            $currentCentroId = $user->centro_id;
        }

        $centro = $currentCentroId ? Centros_Medico::find($currentCentroId) : null;
        $stats = [];

        if ($centro) {
            // Estadísticas del centro específico - Solo las más importantes
            $pacientesCount = \App\Models\Pacientes::forCentro($centro->id)->count();
            $medicosCount = \App\Models\Medico::forCentro($centro->id)->count();
            
            $citasHoy = \App\Models\Citas::forCentro($centro->id)->whereDate('fecha', today());
            $citasPendientes = (clone $citasHoy)->where('estado', 'Pendiente')->count();
            $citasConfirmadas = (clone $citasHoy)->where('estado', 'Confirmado')->count();
            $totalCitasHoy = $citasHoy->count();

            // Solo 4 estadísticas principales
            $stats[] = Stat::make('🏥 ' . $centro->nombre_centro, 'Centro Actual')
                ->description('Centro médico seleccionado')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color(Color::Emerald);

            $stats[] = Stat::make('👥 Pacientes', number_format($pacientesCount))
                ->description('Total registrados')
                ->descriptionIcon('heroicon-m-users')
                ->color(Color::Blue)
                ->url('/admin/pacientes');

            $stats[] = Stat::make('🩺 Médicos', number_format($medicosCount))
                ->description('Personal médico')
                ->descriptionIcon('heroicon-m-user-group')
                ->color(Color::Purple)
                ->url('/admin/medico/medicos');

            $stats[] = Stat::make('📅 Citas Hoy', number_format($totalCitasHoy))
                ->description($citasPendientes > 0 ? "{$citasPendientes} pendientes" : ($citasConfirmadas > 0 ? "{$citasConfirmadas} confirmadas" : "Sin citas"))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($totalCitasHoy > 5 ? Color::Orange : ($totalCitasHoy > 0 ? Color::Green : Color::Gray))
                ->url('/admin/citas/citas');

        } else if ($user && $user->hasRole('root')) {
            // Estadísticas globales para root - Simplificadas
            $totalCentros = Centros_Medico::count();
            $totalPacientes = \App\Models\Pacientes::count();
            $totalMedicos = \App\Models\Medico::count();
            $citasHoyGlobal = \App\Models\Citas::whereDate('fecha', today())->count();
            
            $stats[] = Stat::make('🔧 Vista Global', 'Super Administrador')
                ->description('Acceso a todos los centros')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color(Color::Red);

            $stats[] = Stat::make('🏥 Centros', number_format($totalCentros))
                ->description('Centros médicos')
                ->descriptionIcon('heroicon-m-building-office')
                ->color(Color::Green)
                ->url('/admin/centros-medico/centros-medicos');

            $stats[] = Stat::make('👥 Pacientes', number_format($totalPacientes))
                ->description('Total en sistema')
                ->descriptionIcon('heroicon-m-users')
                ->color(Color::Blue)
                ->url('/admin/pacientes');

            $stats[] = Stat::make('📅 Citas Hoy', number_format($citasHoyGlobal))
                ->description('En todos los centros')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($citasHoyGlobal > 10 ? Color::Orange : Color::Green)
                ->url('/admin/citas/citas');
        }

        return $stats;
    }
}
