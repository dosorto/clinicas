<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Tenant;
use App\Models\Centros_Medico;
use Filament\Support\Colors\Color;

class CentroStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    
    protected string|int|array $columnSpan = 'full';

    protected static ?string $maxHeight = '400px';

    public function getGridColumns(): array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 3,
            'lg' => 4,
            'xl' => 6,
            '2xl' => 7,
        ];
    }

    protected function getColumns(): int
    {
        return 2;
    }

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
                ->color(Color::Green)
                ->chart([1, 2, 3, 4, 5, 6, 7])
                ->extraAttributes([
                    'class' => 'ring-2 ring-green-500/10',
                ]);

            // Contar registros del centro actual (usando scopes para filtrar correctamente)
            $pacientesCount = \App\Models\Pacientes::forCentro($centro->id)->count();
            $medicosCount = \App\Models\Medico::forCentro($centro->id)->count();
            
            // Estadísticas detalladas de citas
            $citasHoy = \App\Models\Citas::forCentro($centro->id)->whereDate('fecha', today());
            $citasPendientes = (clone $citasHoy)->where('estado', 'Pendiente')->count();
            $citasConfirmadas = (clone $citasHoy)->where('estado', 'Confirmado')->count();
            $citasCanceladas = (clone $citasHoy)->where('estado', 'Cancelado')->count();
            $citasRealizadas = (clone $citasHoy)->where('estado', 'Realizada')->count();

            $stats[] = Stat::make('Pacientes', $pacientesCount)
                ->description('Total de pacientes')
                ->descriptionIcon('heroicon-m-users')
                ->color(Color::Blue)
                ->chart([0, $pacientesCount])
                ->url('/admin/pacientes')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors',
                ]);

            $stats[] = Stat::make('Médicos', $medicosCount)
                ->description('Total de médicos')
                ->descriptionIcon('heroicon-m-user-group')
                ->color(Color::Green)
                ->chart([0, $medicosCount])
                ->url('/admin/medico/medicos')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors',
                ]);

            $stats[] = Stat::make('Citas Pendientes', $citasPendientes)
                ->description('Esperando confirmación')
                ->descriptionIcon('heroicon-m-clock')
                ->color(Color::Yellow)
                ->chart([0, $citasPendientes])
                ->url('/admin/citas/citas')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-yellow-50 dark:hover:bg-yellow-900/20 transition-colors',
                ]);

            $stats[] = Stat::make('Citas Confirmadas', $citasConfirmadas)
                ->description('Listas para hoy')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color(Color::Blue)
                ->chart([0, $citasConfirmadas])
                ->url('/admin/citas/citas')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors',
                ]);

            $stats[] = Stat::make('Citas Realizadas', $citasRealizadas)
                ->description('Completadas hoy')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color(Color::Green)
                ->chart([0, $citasRealizadas])
                ->url('/admin/citas/citas')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors',
                ]);

            $stats[] = Stat::make('Citas Canceladas', $citasCanceladas)
                ->description('Canceladas hoy')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color(Color::Red)
                ->chart([0, $citasCanceladas])
                ->url('/admin/citas/citas')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors',
                ]);
        } else if ($user->hasRole('root')) {
            // Estadísticas globales para root
            $totalCentros = Centros_Medico::count();
            $totalPacientes = \App\Models\Pacientes::allCentros()->count();
            $totalMedicos = \App\Models\Medico::allCentros()->count();
            
            // Estadísticas detalladas de citas globales
            $citasHoyGlobal = \App\Models\Citas::allCentros()->whereDate('fecha', today());
            $citasPendientesGlobal = (clone $citasHoyGlobal)->where('estado', 'Pendiente')->count();
            $citasConfirmadasGlobal = (clone $citasHoyGlobal)->where('estado', 'Confirmado')->count();
            $citasCanceladasGlobal = (clone $citasHoyGlobal)->where('estado', 'Cancelado')->count();
            $citasRealizadasGlobal = (clone $citasHoyGlobal)->where('estado', 'Realizada')->count();
            
            $stats[] = Stat::make('Vista Global', 'Administrador')
                ->description('Acceso a todos los centros')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color(Color::Red)
                ->extraAttributes([
                    'class' => 'ring-2 ring-red-500/10',
                ]);

            $stats[] = Stat::make('Centros Médicos', $totalCentros)
                ->description('Total de centros')
                ->descriptionIcon('heroicon-m-building-office')
                ->color(Color::Green)
                ->chart([0, $totalCentros])
                ->url('/admin/centros-medico/centros-medicos')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors',
                ]);

            $stats[] = Stat::make('Pacientes Totales', $totalPacientes)
                ->description('En todos los centros')
                ->descriptionIcon('heroicon-m-users')
                ->color(Color::Blue)
                ->chart([0, $totalPacientes])
                ->url('/admin/pacientes')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors',
                ]);

            $stats[] = Stat::make('Médicos Totales', $totalMedicos)
                ->description('En todos los centros')
                ->descriptionIcon('heroicon-m-user-group')
                ->color(Color::Amber)
                ->chart([0, $totalMedicos])
                ->url('/admin/medico/medicos')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors',
                ]);

            $stats[] = Stat::make('Citas Pendientes', $citasPendientesGlobal)
                ->description('Esperando confirmación')
                ->descriptionIcon('heroicon-m-clock')
                ->color(Color::Yellow)
                ->chart([0, $citasPendientesGlobal])
                ->url('/admin/citas/citas')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-yellow-50 dark:hover:bg-yellow-900/20 transition-colors',
                ]);

            $stats[] = Stat::make('Citas Confirmadas', $citasConfirmadasGlobal)
                ->description('Listas para hoy')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color(Color::Blue)
                ->chart([0, $citasConfirmadasGlobal])
                ->url('/admin/citas/citas')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors',
                ]);
                
            $stats[] = Stat::make('Citas Realizadas', $citasRealizadasGlobal)
                ->description('Completadas hoy')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color(Color::Green)
                ->chart([0, $citasRealizadasGlobal])
                ->url('/admin/citas/citas')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors',
                ]);

            $stats[] = Stat::make('Citas Canceladas', $citasCanceladasGlobal)
                ->description('Canceladas hoy')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color(Color::Red)
                ->chart([0, $citasCanceladasGlobal])
                ->url('/admin/citas/citas')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors',
                ]);
        }

        return $stats;
    }
}
