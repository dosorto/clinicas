<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Tenant;
use App\Models\Centros_Medico;

class CentroStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null; // Desactivar polling automático
    
    protected function getListeners(): array
    {
        return [
            'centro-changed' => '$refresh',
        ];
    }

    protected function getStats(): array
    {
        $currentCentroId = session('current_centro_id');
        $user = auth()->user();
        
        if (!$currentCentroId && !$user->hasRole('root')) {
            $currentCentroId = $user->centro_id;
        }

        $centro = $currentCentroId ? Centros_Medico::find($currentCentroId) : null;
        
        $stats = [];

        if ($centro) {
            $stats[] = Stat::make('Centro Actual', $centro->nombre_centro)
                ->description('Centro médico seleccionado')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('success');

            // Contar registros del centro actual (usando scopes para filtrar correctamente)
            $pacientesCount = \App\Models\Pacientes::forCentro($centro->id)->count();
            $medicosCount = \App\Models\Medico::forCentro($centro->id)->count();
            $citasCount = \App\Models\Citas::forCentro($centro->id)
                ->whereDate('fecha', today())->count();

            $stats[] = Stat::make('Pacientes', $pacientesCount)
                ->description('Total de pacientes')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary');

            $stats[] = Stat::make('Médicos', $medicosCount)
                ->description('Total de médicos')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning');

            $stats[] = Stat::make('Citas Hoy', $citasCount)
                ->description('Citas programadas para hoy')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info');
        } else if ($user->hasRole('root')) {
            // Estadísticas globales para root
            $totalCentros = Centros_Medico::count();
            $totalPacientes = \App\Models\Pacientes::allCentros()->count();
            
            $stats[] = Stat::make('Vista Global', 'Administrador')
                ->description('Acceso a todos los centros')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('danger');

            $stats[] = Stat::make('Centros Médicos', $totalCentros)
                ->description('Total de centros')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('success');

            $stats[] = Stat::make('Pacientes Totales', $totalPacientes)
                ->description('En todos los centros')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary');
        }

        return $stats;
    }
}
