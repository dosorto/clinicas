<?php

namespace App\Filament\Widgets;

use App\Models\Citas;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Filament\Notifications\Notification;

class CalendarioCitasWidget extends Widget
{
    protected static string $view = 'filament.widgets.calendario-citas-widget';
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '30s';
    protected static ?int $sort = 2;
    
    // Propiedades para el calendario
    public array $citas = [];
    public array $citasPorDia = [];
    public string $mes;
    public string $anio;
    public string $mesActual;
    
    // Propiedades para el modal
    public ?int $citaSeleccionadaId = null;
    public ?string $diaSeleccionado = null;
    public array $citasDelDia = [];
    public ?string $fechaSeleccionadaUrl = null;

    public function mount(): void
    {
        $this->mes = session('calendario_mes', Carbon::now()->format('m'));
        $this->anio = session('calendario_anio', Carbon::now()->format('Y'));
        $this->mesActual = Carbon::createFromDate($this->anio, $this->mes, 1)->locale('es')->monthName;
        
        session(['calendario_mes' => $this->mes]);
        session(['calendario_anio' => $this->anio]);
        
        $this->cargarCitas();
    }

    public static function getSort(): int
    {
        return 2;
    }
    
    /**
     * Mostrar modal con citas del día
     */
    public function mostrarCitasDelDia(string $dia, ?int $citaId = null): void
    {
        $this->diaSeleccionado = $dia . ' de ' . $this->mesActual . ' ' . $this->anio;
        
        $fechaSeleccionada = Carbon::createFromDate($this->anio, $this->mes, intval($dia));
        $this->fechaSeleccionadaUrl = $fechaSeleccionada->format('Y-m-d');
        
        if ($citaId !== null) {
            foreach ($this->citasPorDia as $citas) {
                foreach ($citas as $cita) {
                    if ($cita['id'] == $citaId) {
                        $this->citasDelDia = [$cita];
                        break 2;
                    }
                }
            }
        } else {
            $this->citasDelDia = $this->citasPorDia[intval($dia)] ?? [];
        }
        
        $this->dispatch('open-modal', id: 'citas-del-dia-modal');
    }
    
    /**
     * Actualiza el estado de una cita en memoria
     */
    private function actualizarEstadoCitaEnMemoria(int $id, string $nuevoEstado): void
    {
        foreach ($this->citasPorDia as $dia => $citas) {
            foreach ($citas as $idx => $citaData) {
                if ($citaData['id'] == $id) {
                    $this->citasPorDia[$dia][$idx]['estado'] = $nuevoEstado;
                    $this->citasPorDia[$dia][$idx]['color'] = $this->getColorForEstado($nuevoEstado);
                }
            }
        }
        
        foreach ($this->citasDelDia as $idx => $citaData) {
            if ($citaData['id'] == $id) {
                $this->citasDelDia[$idx]['estado'] = $nuevoEstado;
                $this->citasDelDia[$idx]['color'] = $this->getColorForEstado($nuevoEstado);
            }
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
        try {
            $fecha = Carbon::createFromDate($this->anio, $this->mes, 1)->subMonth();
            $this->mes = $fecha->format('m');
            $this->anio = $fecha->format('Y');
            $this->mesActual = $fecha->locale('es')->monthName;
            
            session(['calendario_mes' => $this->mes]);
            session(['calendario_anio' => $this->anio]);
            
            $this->cargarCitas();
            $this->dispatch('limpiar-modal-calendario');
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al cambiar de mes')
                ->body('No se pudo navegar al mes anterior: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * Navegar al mes siguiente
     */
    public function mesSiguiente()
    {
        try {
            $fecha = Carbon::createFromDate($this->anio, $this->mes, 1)->addMonth();
            $this->mes = $fecha->format('m');
            $this->anio = $fecha->format('Y');
            $this->mesActual = $fecha->locale('es')->monthName;
            
            session(['calendario_mes' => $this->mes]);
            session(['calendario_anio' => $this->anio]);
            
            $this->cargarCitas();
            $this->dispatch('limpiar-modal-calendario');
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al cambiar de mes')
                ->body('No se pudo navegar al mes siguiente: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * Ir al mes actual
     */
    public function irHoy()
    {
        try {
            $fecha = Carbon::now();
            $this->mes = $fecha->format('m');
            $this->anio = $fecha->format('Y');
            $this->mesActual = $fecha->locale('es')->monthName;
            
            session(['calendario_mes' => $this->mes]);
            session(['calendario_anio' => $this->anio]);
            
            $this->cargarCitas();
            $this->dispatch('limpiar-modal-calendario');
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al ir al mes actual')
                ->body('No se pudo navegar al mes actual: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    /**
     * Cargar las citas para el mes actual
     */
    protected function cargarCitas()
    {
        $this->citas = [];
        $this->citasPorDia = [];
        
        $medico = Auth::user()->medico;
        
        if ($medico) {
            $fechaInicio = Carbon::createFromDate($this->anio, $this->mes, 1)->startOfMonth();
            $fechaFin = Carbon::createFromDate($this->anio, $this->mes, 1)->endOfMonth();
            
            $withRelations = ['paciente.persona', 'medico.persona', 'medico.especialidades'];
            
            $citaModel = new Citas();
            $availableRelations = get_class_methods($citaModel);
            if (in_array('especialidad', $availableRelations)) {
                $withRelations[] = 'especialidad';
            }
            if (in_array('especialidad_medico', $availableRelations)) {
                $withRelations[] = 'especialidad_medico';
            }
            
            $this->citas = Citas::query()
                ->where('medico_id', $medico->id)
                ->where('estado', '!=', 'Cancelado')
                ->whereBetween('fecha', [$fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d')])
                ->with($withRelations)
                ->get()
                ->map(function ($cita) {
                    $fecha = Carbon::parse($cita->fecha);
                    $hora = Carbon::parse($cita->hora)->format('H:i');
                    $pacienteNombre = $cita->paciente->persona->nombre_completo ?? 'Paciente sin nombre';
                    
                    $especialidad = '';
                    if (isset($cita->especialidad_id) && isset($cita->especialidad) && is_object($cita->especialidad)) {
                        $especialidad = $cita->especialidad->nombre ?? '';
                    } elseif (isset($cita->especialidad_medico) && is_object($cita->especialidad_medico)) {
                        $especialidad = $cita->especialidad_medico->nombre ?? '';
                    } elseif (isset($cita->medico) && isset($cita->medico->especialidades) && $cita->medico->especialidades->isNotEmpty()) {
                        $especialidad = $cita->medico->especialidades->first()->nombre ?? '';
                    }
                    
                    return [
                        'id' => $cita->id,
                        'fecha' => $fecha->format('Y-m-d'),
                        'dia' => $fecha->day,
                        'hora' => $hora,
                        'paciente' => $pacienteNombre,
                        'paciente_id' => $cita->paciente_id,
                        'medico_id' => $cita->medico_id,
                        'motivo' => $cita->motivo,
                        'estado' => $cita->estado,
                        'color' => $this->getColorForEstado($cita->estado),
                        'medico' => $cita->medico->persona->nombre_completo ?? 'Médico',
                        'especialidad' => $especialidad,
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
     * Cancelar una cita
     */
    public function cancelarCita($citaId)
    {
        try {
            $cita = Citas::find($citaId);
            
            if ($cita) {
                $cita->fill(['estado' => 'Cancelado']);
                $cita->save();
                
                $this->actualizarEstadoCitaEnMemoria($citaId, 'Cancelado');
                
                Notification::make()
                    ->title('Cita cancelada')
                    ->body('La cita ha sido cancelada correctamente')
                    ->success()
                    ->send();
                    
                return [
                    'id' => $cita->id,
                    'estado' => 'Cancelado'
                ];
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al cancelar cita')
                ->body('No se pudo cancelar la cita: ' . $e->getMessage())
                ->danger()
                ->send();
        }
        
        return false;
    }
    
    /**
     * Confirmar una cita
     */
    public function confirmarCita($citaId)
    {
        try {
            $cita = Citas::find($citaId);
            
            if ($cita) {
                $cita->fill(['estado' => 'Confirmado']);
                $cita->save();
                
                $this->actualizarEstadoCitaEnMemoria($citaId, 'Confirmado');
                
                Notification::make()
                    ->title('Cita confirmada')
                    ->body('La cita ha sido confirmada correctamente')
                    ->success()
                    ->send();
                    
                return [
                    'id' => $cita->id,
                    'estado' => 'Confirmado'
                ];
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al confirmar cita')
                ->body('No se pudo confirmar la cita: ' . $e->getMessage())
                ->danger()
                ->send();
        }
        
        return false;
    }
    
    /**
     * Crear consulta desde una cita
     */
    public function crearConsulta($citaId)
    {
        try {
            $cita = Citas::with('paciente')->find($citaId);
            
            if ($cita) {
                session(['cita_en_consulta' => $citaId]);
                
                Notification::make()
                    ->title('Redirigiendo a creación de consulta')
                    ->body('Creando consulta para el paciente ' . ($cita->paciente->persona->nombre_completo ?? 'Desconocido'))
                    ->success()
                    ->send();
                    
                $urlBase = url('/');
                $redirectUrl = "{$urlBase}/admin/consultas/consultas/create?paciente_id={$cita->paciente_id}&cita_id={$citaId}";
                
                $this->dispatch('redirigirConsulta', url: $redirectUrl);
                
                return true;
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al crear consulta')
                ->body('No se pudo crear la consulta: ' . $e->getMessage())
                ->danger()
                ->send();
        }
        
        return false;
    }
}