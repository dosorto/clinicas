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
            'md' => 4,
            'lg' => 6,
            'xl' => 8,
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
            // Estadísticas del centro específico
            $pacientesCount = \App\Models\Pacientes::forCentro($centro->id)->count();
            $medicosCount = \App\Models\Medico::forCentro($centro->id)->count();
            
            $citasHoy = \App\Models\Citas::forCentro($centro->id)->whereDate('fecha', today());
            $citasPendientes = (clone $citasHoy)->where('estado', 'Pendiente')->count();
            $citasConfirmadas = (clone $citasHoy)->where('estado', 'Confirmado')->count();
            $citasCanceladas = (clone $citasHoy)->where('estado', 'Cancelado')->count();
            $citasRealizadas = (clone $citasHoy)->where('estado', 'Realizada')->count();
            $totalCitasHoy = $citasHoy->count();

            $stats[] = Stat::make('🏥 Centro Médico', $centro->nombre_centro)
                ->description('Centro seleccionado')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color(Color::Emerald)
                ->extraAttributes([
                    'class' => 'bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 border-emerald-200 dark:border-emerald-800',
                ]);

            $stats[] = Stat::make('👥 Pacientes', number_format($pacientesCount))
                ->description('Total registrados')
                ->descriptionIcon('heroicon-m-users')
                ->color(Color::Blue)
                ->chart([max(1, $pacientesCount-10), max(1, $pacientesCount-5), $pacientesCount])
                ->url('/admin/pacientes')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:scale-105 transition-transform duration-200 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-blue-200 dark:border-blue-800',
                ]);

            $stats[] = Stat::make('🩺 Médicos', number_format($medicosCount))
                ->description('Personal médico')
                ->descriptionIcon('heroicon-m-user-group')
                ->color(Color::Purple)
                ->chart([max(1, $medicosCount-2), $medicosCount, max(1, $medicosCount+1)])
                ->url('/admin/medico/medicos')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:scale-105 transition-transform duration-200 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-purple-200 dark:border-purple-800',
                ]);

            $stats[] = Stat::make('📅 Citas de Hoy', number_format($totalCitasHoy))
                ->description('Total programadas')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($totalCitasHoy > 10 ? Color::Red : ($totalCitasHoy > 5 ? Color::Amber : Color::Green))
                ->chart([$citasPendientes, $citasConfirmadas, $citasRealizadas, max(1, $citasCanceladas)])
                ->url('/admin/citas/citas')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:scale-105 transition-transform duration-200 bg-gradient-to-br from-gray-50 to-slate-50 dark:from-gray-900/20 dark:to-slate-900/20 border-gray-200 dark:border-gray-800',
                ]);

            $stats[] = Stat::make('👥 Pacientes', $pacientesCount)
                ->description('Total registrados')
                ->descriptionIcon('heroicon-m-users')
                ->color(Color::Blue)
                ->chart([max(1, $pacientesCount-10), max(1, $pacientesCount-5), $pacientesCount])
                ->url('/admin/pacientes')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-200 transform hover:scale-105',
                ]);

            $stats[] = Stat::make('🩺 Médicos', $medicosCount)
                ->description('Personal médico')
                ->descriptionIcon('heroicon-m-user-group')
                ->color(Color::Purple)
                ->chart([max(1, $medicosCount-2), $medicosCount, max(1, $medicosCount+1)])
                ->url('/admin/medico/medicos')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-all duration-200 transform hover:scale-105',
                ]);

            $stats[] = Stat::make('📅 Citas Hoy', $totalCitasHoy)
                ->description('Total programadas')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($totalCitasHoy > 10 ? Color::Red : ($totalCitasHoy > 5 ? Color::Yellow : Color::Green))
                ->chart([$citasPendientes, $citasConfirmadas, $citasRealizadas, $citasCanceladas])
                ->url('/admin/citas/citas')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900/20 transition-all duration-200 transform hover:scale-105',
                ]);

            $stats[] = Stat::make('⏳ Pendientes', $citasPendientes)
                ->description('Esperando confirmación')
                ->descriptionIcon('heroicon-m-clock')
                ->color(Color::Amber)
                ->chart([0, $citasPendientes, max(1, $citasPendientes)])
                ->url('/admin/citas/citas?filter[estado]=Pendiente')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-all duration-200 transform hover:scale-105',
                ]);

            $stats[] = Stat::make('✅ Confirmadas', $citasConfirmadas)
                ->description('Listas para hoy')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color(Color::Cyan)
                ->chart([0, max(1, $citasConfirmadas-1), $citasConfirmadas])
                ->url('/admin/citas/citas?filter[estado]=Confirmado')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-cyan-50 dark:hover:bg-cyan-900/20 transition-all duration-200 transform hover:scale-105',
                ]);

            $stats[] = Stat::make('✨ Realizadas', $citasRealizadas)
                ->description('Completadas hoy')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color(Color::Green)
                ->chart([0, max(1, $citasRealizadas-1), $citasRealizadas])
                ->url('/admin/citas/citas?filter[estado]=Realizada')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-green-50 dark:hover:bg-green-900/20 transition-all duration-200 transform hover:scale-105',
                ]);

            $stats[] = Stat::make('❌ Canceladas', $citasCanceladas)
                ->description('Canceladas hoy')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color(Color::Red)
                ->chart([0, max(1, $citasCanceladas-1), $citasCanceladas])
                ->url('/admin/citas/citas?filter[estado]=Cancelado')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200 transform hover:scale-105',
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
