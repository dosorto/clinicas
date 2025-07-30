<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Citas;
use App\Models\Pacientes;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarioCitas extends Page
{
    /* ───────── Configuración de la Page ───────── */
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static string $view = 'filament.pages.calendario-citas';

    /* ───────── Datos que pasaremos a la vista ─── */
    public array $citas = [];
    public array $citasPorDia = [];
    public string $mes;
    public string $anio;
    public string $mesActual;
    public ?int $citaIdConfirmacion = null; // ID de cita para confirmar
    public ?int $citaIdCancelacion = null; // ID de cita para cancelar

    /* ───────── Cargar eventos al montar la Page ─ */
    public function mount(): void
    {
        $this->mes = request('mes') ?? Carbon::now()->format('m');
        $this->anio = request('anio') ?? Carbon::now()->format('Y');
        $this->mesActual = Carbon::createFromDate($this->anio, $this->mes, 1)->locale('es')->monthName;
        
        $medico = Auth::user()->medico;
        
        if ($medico) {
            $fechaInicio = Carbon::createFromDate($this->anio, $this->mes, 1)->startOfMonth();
            $fechaFin = Carbon::createFromDate($this->anio, $this->mes, 1)->endOfMonth();
            
            $this->citas = Citas::query()
                ->where('medico_id', $medico->id)
                ->where('estado', '!=', 'Cancelado')
                ->whereBetween('fecha', [$fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d')])
                ->with(['paciente.persona'])
                ->get()
                ->map(function ($cita) {
                    $fecha = Carbon::parse($cita->fecha);
                    $hora = Carbon::parse($cita->hora)->format('H:i');
                    $pacienteNombre = $cita->paciente->persona->nombre_completo ?? 'Paciente sin nombre';
                    
                    return [
                        'id' => $cita->id,
                        'fecha' => $fecha->format('Y-m-d'),
                        'dia' => $fecha->day,
                        'hora' => $hora,
                        'paciente' => $pacienteNombre,
                        'motivo' => $cita->motivo,
                        'estado' => $cita->estado,
                        'color' => $this->getColorForEstado($cita->estado),
                    ];
                })
                ->toArray();
                
            // Agrupar citas por día
            $this->citasPorDia = collect($this->citas)
                ->groupBy('dia')
                ->map(function ($items) {
                    return $items->sortBy('hora')->values()->toArray();
                })
                ->toArray();
        }
    }

    /**
     * Obtener color según estado de la cita
     */
    protected function getColorForEstado(string $estado): string
    {
        return match($estado) {
            'Confirmado' => '#3b82f6', // blue-500
            'Pendiente' => '#f97316',  // orange-500
            'Cancelado' => '#ef4444',  // red-500
            'Realizada' => '#22c55e',  // green-500
            default => '#6b7280',      // gray-500
        };
    }
    
    /**
     * Navegar al mes anterior
     */
    public function mesAnterior()
    {
        $fecha = Carbon::createFromDate($this->anio, $this->mes, 1)->subMonth();
        $this->mes = $fecha->format('m');
        $this->anio = $fecha->format('Y');
        $this->mesActual = $fecha->locale('es')->monthName;
        $this->mount();
        
        // Asegurarnos de que la vista se actualice
        $this->dispatch('mesActualizado');
    }
    
    /**
     * Navegar al mes siguiente
     */
    public function mesSiguiente()
    {
        $fecha = Carbon::createFromDate($this->anio, $this->mes, 1)->addMonth();
        $this->mes = $fecha->format('m');
        $this->anio = $fecha->format('Y');
        $this->mesActual = $fecha->locale('es')->monthName;
        $this->mount();
        
        // Asegurarnos de que la vista se actualice
        $this->dispatch('mesActualizado');
    }
    
    /**
     * Ir al mes actual
     */
    public function hoy()
    {
        $fecha = Carbon::now();
        $this->mes = $fecha->format('m');
        $this->anio = $fecha->format('Y');
        $this->mesActual = $fecha->locale('es')->monthName;
        $this->mount();
        
        // Asegurarnos de que la vista se actualice
        $this->dispatch('mesActualizado');
    }
    
    /**
     * Cancelar una cita
     */
    public function cancelarCita($citaId)
    {
        $cita = Citas::find($citaId);
        
        if ($cita) {
            // Cambiar el estado a Cancelada usando fill para formato correcto
            $cita->fill(['estado' => 'Cancelado']);
            $cita->save();
            
            // Notificar al usuario
            \Filament\Notifications\Notification::make()
                ->title('Cita cancelada')
                ->body('La cita ha sido cancelada correctamente')
                ->success()
                ->send();
                
            // Recargar las citas para actualizar la vista
            $this->mount();
            
            // Emitir evento para actualizar la interfaz
            $this->dispatch('citasActualizadas');
            
            // Devolver datos para actualización inmediata en el frontend
            return [
                'id' => $cita->id,
                'estado' => 'Cancelado'
            ];
        }
        
        return false;
    }
    
    /**
     * Confirmar una cita
     */
    public function confirmarCita($citaId)
    {
        $cita = Citas::find($citaId);
        
        if ($cita) {
            // Cambiar el estado a Confirmada usando fill para formato correcto
            $cita->fill(['estado' => 'Confirmado']);
            $cita->save();
            
            // Notificar al usuario
            \Filament\Notifications\Notification::make()
                ->title('Cita confirmada')
                ->body('La cita ha sido confirmada correctamente')
                ->success()
                ->send();
                
            // Recargar las citas para actualizar la vista
            $this->mount();
            
            // Emitir evento para actualizar la interfaz
            $this->dispatch('citasActualizadas');
            
            // Devolver datos para actualización inmediata en el frontend
            return [
                'id' => $cita->id,
                'estado' => 'Confirmado'
            ];
        }
        
        return false;
    }
    
    /**
     * Redireccionar a la página de creación de consulta con los datos pre-llenados
     */
    public function crearConsulta($citaId)
    {
        $cita = Citas::with('paciente')->find($citaId);
        
        if ($cita) {
            // Marcamos que esta cita está en proceso de consulta
            session(['cita_en_consulta' => $citaId]);
            
            // Notificar al usuario antes de redireccionar
            \Filament\Notifications\Notification::make()
                ->title('Redirigiendo a creación de consulta')
                ->body('Creando consulta para el paciente ' . ($cita->paciente->persona->nombre_completo ?? 'Desconocido'))
                ->success()
                ->send();
                
            // Emitir evento para actualizar la interfaz
            $this->dispatch('citasActualizadas');
            
            // Redireccionar a la página de creación de consulta con parámetros
            $redirectUrl = '/admin/consultas/consultas/create?paciente_id=' . $cita->paciente_id . '&cita_id=' . $citaId;
            
            // La redirección maneja el estado internamente, así que no podemos
            // actualizar la UI inmediatamente como con los otros métodos
            return redirect()->to($redirectUrl);
        }
        
        return null;
    }
}
