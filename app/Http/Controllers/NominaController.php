<?php

namespace App\Http\Controllers;

use App\Models\ContabilidadMedica\ContratoMedico;
use App\Models\Medico;
use App\Models\ContabilidadMedica\LiquidacionHonorario;
use App\Models\ContabilidadMedica\PagoHonorario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class NominaController extends Controller
{
    /**
     * Generar la nómina médica en PDF
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generarPDF(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'medico_id' => 'nullable|exists:medicos,id',
            'incluir_pagados' => 'boolean',
            'incluir_pendientes' => 'boolean',
        ]);

        $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
        $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();
        $medicoId = $request->medico_id;
        $incluirPagados = $request->incluir_pagados ?? true;
        $incluirPendientes = $request->incluir_pendientes ?? false;

        // Obtener el centro médico actual del usuario
        $centroActual = null;
        try {
            if (Auth::check() && Auth::user()->centro_id) {
                $centroActual = Auth::user()->centro_id;
            }
        } catch (\Exception $e) {
            // Si no se puede obtener el centro, continuar sin filtro
        }

        // Obtener médicos según el filtro y centro médico
        $medicosQuery = Medico::with(['persona', 'especialidades', 'centro']);
        
        // Filtrar por centro médico si está disponible
        if ($centroActual) {
            $medicosQuery->where('centro_id', $centroActual);
        }
        
        // Filtrar por médico específico si se proporciona
        if ($medicoId) {
            $medicosQuery->where('id', $medicoId);
        }
        
        $medicos = $medicosQuery->get();

        $resultados = [];
        $totalGeneral = 0;
        $totalRetenciones = 0;
        $totalNeto = 0;

        foreach ($medicos as $medico) {
            // Obtener el contrato vigente del médico
            $contrato = ContratoMedico::where('medico_id', $medico->id)
                ->where('fecha_inicio', '<=', $fechaFin)
                ->where(function ($query) use ($fechaInicio) {
                    $query->where('fecha_fin', '>=', $fechaInicio)
                        ->orWhereNull('fecha_fin');
                })
                ->orderBy('fecha_inicio', 'desc')
                ->first();

            // Si no hay contrato, crear datos básicos para el médico
            if (!$contrato) {
                // Agregar médico sin contrato con datos básicos
                $resultados[] = [
                    'medico' => $medico,
                    'nombre_medico' => $medico->persona ? 
                        $medico->persona->nombre_completo : 
                        'Médico #' . $medico->id,
                    'especialidad' => $medico->especialidades->first()->especialidad ?? 'No especificada',
                    'centro' => $medico->centro->nombre ?? 'No especificado',
                    'contrato' => null,
                    'porcentaje_medico' => 0,
                    'liquidaciones' => collect([]),
                    'pagos' => collect([]),
                    'total_liquidaciones' => 0,
                    'total_pagado' => 0,
                    'total_retenciones' => 0,
                    'monto_neto' => 0,
                    'total_pendiente' => 0,
                    'salario_base' => 0,
                    'mensaje' => 'Sin contrato vigente en el período'
                ];
                continue;
            }

            // Liquidaciones del periodo
            $liquidacionesQuery = LiquidacionHonorario::where('medico_id', $medico->id)
                ->where('periodo_inicio', '>=', $fechaInicio)
                ->where('periodo_fin', '<=', $fechaFin);
            
            if (!$incluirPendientes) {
                $liquidacionesQuery->where('estado', '!=', 'PENDIENTE');
            }
            
            $liquidaciones = $liquidacionesQuery->get();

            // Pagos del periodo  
            $pagosQuery = PagoHonorario::whereIn('liquidacion_id', $liquidaciones->pluck('id'))
                ->where('fecha_pago', '>=', $fechaInicio)
                ->where('fecha_pago', '<=', $fechaFin);
            
            $pagos = $pagosQuery->get();

            // Calcular totales
            $totalLiquidaciones = $liquidaciones->sum('monto_total');
            $totalPagado = $pagos->sum('monto_pagado');
            $totalRetencionesMedico = $pagos->sum('retencion_isr_monto');
            $montoNeto = $totalPagado - $totalRetencionesMedico;
            $totalPendiente = $totalLiquidaciones - $totalPagado;

            // Calcular salario base del período
            $salarioBase = $contrato->salario_mensual ?? 0;
            $diasPeriodo = $fechaInicio->diffInDays($fechaFin) + 1;
            $diasMes = $fechaInicio->daysInMonth;
            $salarioPeriodo = ($salarioBase / $diasMes) * $diasPeriodo;

            // Acumular totales generales
            $totalGeneral += ($totalPagado + $salarioPeriodo);
            $totalRetenciones += $totalRetencionesMedico;
            $totalNeto += ($montoNeto + $salarioPeriodo);

            // Agregar todos los médicos con contrato, tengan o no liquidaciones
            $resultados[] = [
                'medico' => $medico,
                'nombre_medico' => $medico->persona ? 
                    $medico->persona->nombre_completo : 
                    'Médico #' . $medico->id,
                'especialidad' => $medico->especialidades->first()->especialidad ?? 'No especificada',
                'centro' => $medico->centro->nombre ?? 'No especificado',
                'contrato' => $contrato,
                'porcentaje_medico' => $contrato ? $contrato->porcentaje_servicio : 0,
                'liquidaciones' => $liquidaciones,
                'pagos' => $pagos,
                'total_liquidaciones' => $totalLiquidaciones,
                'total_pagado' => $totalPagado,
                'total_retenciones' => $totalRetencionesMedico,
                'monto_neto' => $montoNeto,
                'total_pendiente' => $totalPendiente,
                'salario_base' => $salarioBase,
                'salario_periodo' => $salarioPeriodo,
                'total_con_salario' => $montoNeto + $salarioPeriodo,
                'mensaje' => $liquidaciones->count() == 0 ? 'Sin liquidaciones en el período' : null
            ];
        }

        // Obtener información del centro médico
        $centroMedico = null;
        if ($centroActual) {
            $centroMedico = \App\Models\Centros_Medico::find($centroActual);
        }

        // Generar el PDF
        $data = [
            'resultados' => $resultados,
            'periodo_inicio' => $fechaInicio->format('d/m/Y'),
            'periodo_fin' => $fechaFin->format('d/m/Y'),
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i'),
            'total_general' => $totalGeneral,
            'total_retenciones' => $totalRetenciones,
            'total_neto' => $totalNeto,
            'centro_medico' => $centroMedico
        ];

        $pdf = PDF::loadView('pdf.nomina', $data);
        
        // Establecer opciones de PDF
        $pdf->setPaper('letter', 'portrait');
        $pdf->setOption('margin-top', 10);
        $pdf->setOption('margin-right', 10);
        $pdf->setOption('margin-bottom', 10);
        $pdf->setOption('margin-left', 10);
        
        $filename = 'nomina_medica_' . $fechaInicio->format('d-m-Y') . '_' . $fechaFin->format('d-m-Y') . '.pdf';
        
        return $pdf->download($filename);
    }
}
