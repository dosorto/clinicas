<?php

namespace App\Filament\Resources\ContabilidadMedica\Widgets;

use App\Models\ContabilidadMedica\CargoMedico;
use App\Models\ContabilidadMedica\PagoHonorario;
use App\Models\ContabilidadMedica\LiquidacionHonorario;
use App\Models\Medico;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ResumenMedicoContabilidad extends Widget
{
    protected static string $view = 'filament.resources.contabilidad-medica.widgets.resumen-medico-contabilidad';
    
    // Solo mostrar en la vista de un médico específico
    public ?Medico $record = null;
    
    public function mount(?Medico $record = null): void
    {
        $this->record = $record;
    }
    
    public function getContratosActivos()
    {
        if (!$this->record) return [];
        
        return $this->record->contratos()
            ->where('activo', true)
            ->where('fecha_fin', '>=', now())
            ->with('centro')
            ->get();
    }
    
    public function getPagosPorMes()
    {
        if (!$this->record) return [];
        
        $meses = [];
        $datos = [];
        
        // Últimos 6 meses
        for ($i = 0; $i < 6; $i++) {
            $fecha = Carbon::now()->subMonths($i);
            $mes = $fecha->format('M Y');
            $meses[] = $mes;
            
            $inicioMes = $fecha->copy()->startOfMonth()->format('Y-m-d');
            $finMes = $fecha->copy()->endOfMonth()->format('Y-m-d');
            
            // Obtener pagos realizados en este mes
            $pagos = PagoHonorario::whereHas('liquidacion', function ($query) {
                    $query->where('medico_id', $this->record->id);
                })
                ->whereBetween('fecha_pago', [$inicioMes, $finMes])
                ->sum('monto');
                
            $datos[] = round($pagos, 2);
        }
        
        return [
            'labels' => array_reverse($meses),
            'data' => array_reverse($datos),
        ];
    }
    
    public function getResumenContable()
    {
        if (!$this->record) return [];
        
        // Total pendiente
        $pendiente = CargoMedico::where('medico_id', $this->record->id)
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->sum('total');
        
        // Total pagado (último mes)
        $inicioMes = Carbon::now()->startOfMonth()->format('Y-m-d');
        $finMes = Carbon::now()->endOfMonth()->format('Y-m-d');
        
        $pagadoMes = PagoHonorario::whereHas('liquidacion', function ($query) {
                $query->where('medico_id', $this->record->id);
            })
            ->whereBetween('fecha_pago', [$inicioMes, $finMes])
            ->sum('monto');
            
        // Total pagado (año actual)
        $inicioAno = Carbon::now()->startOfYear()->format('Y-m-d');
        $finAno = Carbon::now()->endOfYear()->format('Y-m-d');
        
        $pagadoAno = PagoHonorario::whereHas('liquidacion', function ($query) {
                $query->where('medico_id', $this->record->id);
            })
            ->whereBetween('fecha_pago', [$inicioAno, $finAno])
            ->sum('monto');
            
        return [
            'pendiente' => $pendiente,
            'pagado_mes' => $pagadoMes,
            'pagado_ano' => $pagadoAno,
        ];
    }
    
    public function getCargosRecientes()
    {
        if (!$this->record) return [];
        
        return CargoMedico::where('medico_id', $this->record->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }
    
    public function getPagosRecientes()
    {
        if (!$this->record) return [];
        
        return PagoHonorario::whereHas('liquidacion', function ($query) {
                $query->where('medico_id', $this->record->id);
            })
            ->with('liquidacion')
            ->orderBy('fecha_pago', 'desc')
            ->limit(5)
            ->get();
    }
}
