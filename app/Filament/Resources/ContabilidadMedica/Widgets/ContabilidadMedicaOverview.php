<?php

namespace App\Filament\Resources\ContabilidadMedica\Widgets;

use App\Models\ContabilidadMedica\CargoMedico;
use App\Models\ContabilidadMedica\PagoCargoMedico;
use App\Models\Medico;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContabilidadMedicaOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    
    // Solo mostrar en el contexto del grupo "Contabilidad Médica"
    public static function canView(): bool
    {
        return request()->routeIs('filament.admin.resources.contabilidad-medica.*') ||
               request()->routeIs('filament.admin.pages.contabilidad-medica-dashboard');
    }

    protected function getStats(): array
    {
        $centroId = Auth::user()->centro_id;
        
        // Obtener estadísticas filtradas por el centro del usuario actual
        $query = CargoMedico::query();
        $pagoQuery = PagoCargoMedico::query();
        
        if ($centroId) {
            $query->where('centro_id', $centroId);
            $pagoQuery->where('centro_id', $centroId);
        }
        
        // Calcular montos pendientes y pagados
        $totalPendiente = $query->where('estado', 'pendiente')->sum('total');
        $totalParcial = $query->where('estado', 'parcial')->sum('total');
        $totalPagado = $pagoQuery->sum('monto_pagado');
        
        // Calcular médicos con pagos pendientes
        $medicosPendientes = $query->whereIn('estado', ['pendiente', 'parcial'])
            ->distinct('medico_id')
            ->count('medico_id');

        return [
            Stat::make('Pagos Pendientes', 'L. ' . number_format($totalPendiente, 2))
                ->description('Total pendiente por pagar')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger')
                ->chart([7, 4, 6, 8, 5, 8, 3])
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'wire:click' => 'redirectToPendientes',
                ]),
            
            Stat::make('Pagos Parciales', 'L. ' . number_format($totalParcial, 2))
                ->description('Pagos realizados parcialmente')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning')
                ->chart([4, 5, 3, 7, 8, 5, 6]),
                
            Stat::make('Total Pagado', 'L. ' . number_format($totalPagado, 2))
                ->description('Pagos realizados en el mes actual')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([3, 5, 7, 6, 8, 9, 10]),
                
            Stat::make('Médicos con Pagos Pendientes', $medicosPendientes)
                ->description('Médicos esperando pago')
                ->descriptionIcon('heroicon-m-user')
                ->color('info')
                ->chart([$medicosPendientes, $medicosPendientes-1, $medicosPendientes+2, $medicosPendientes, $medicosPendientes-2, $medicosPendientes+1, $medicosPendientes]),
        ];
    }
    
    // Método para redireccionar a los pagos pendientes
    public function redirectToPendientes()
    {
        // Redireccionar a la lista de cargos médicos filtrados por estado pendiente
        return redirect()->route('filament.admin.resources.contabilidad-medica.cargo-medicos.index', [
            'tableFilters[estado][value]' => 'pendiente',
        ]);
    }
}
