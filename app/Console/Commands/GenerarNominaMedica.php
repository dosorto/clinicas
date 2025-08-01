<?php

namespace App\Console\Commands;

use App\Models\Medico;
use App\Models\Centros_Medico;
use App\Models\ContabilidadMedica\LiquidacionHonorario;
use App\Models\ContabilidadMedica\PagoHonorario;
use App\Models\ContabilidadMedica\ContratoMedico;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerarNominaMedica extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contabilidad:generar-nomina
                            {--centro_id= : ID del centro médico específico}
                            {--medico_id=* : ID(s) del médico(s) específico(s), separados por espacio para múltiples}
                            {--periodo=mensual : Periodo de la nómina (mensual, quincenal)}
                            {--inicio= : Fecha de inicio del periodo (formato: Y-m-d)}
                            {--fin= : Fecha de fin del periodo (formato: Y-m-d)}
                            {--descargar : Descargar PDF automáticamente}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera la nómina médica para un periodo específico';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando generación de nómina médica...');
        
        $centroId = $this->option('centro_id');
        $medicosIds = $this->option('medico_id');
        $periodo = $this->option('periodo');
        
        // Determinar fechas del periodo
        $fechaInicio = $this->option('inicio') ? Carbon::parse($this->option('inicio')) : null;
        $fechaFin = $this->option('fin') ? Carbon::parse($this->option('fin')) : null;
        
        if (!$fechaInicio || !$fechaFin) {
            $this->calcularPeriodoAutomatico($periodo, $fechaInicio, $fechaFin);
        }
        
        $this->info("Periodo de nómina: {$fechaInicio->format('d/m/Y')} - {$fechaFin->format('d/m/Y')}");
        
        // Si se especificó un centro, lo establecemos como tenant
        if ($centroId) {
            $centro = Centros_Medico::find($centroId);
            if (!$centro) {
                $this->error("Centro médico con ID {$centroId} no encontrado.");
                return 1;
            }
            $tenant = Tenant::findOrCreateForCentro($centro);
            $tenant->makeCurrent();
            $resultados = $this->procesarCentro($centro, $medicosIds, $fechaInicio, $fechaFin);
        } else {
            // Procesar todos los centros
            $centros = Centros_Medico::all();
            $this->info("Procesando {$centros->count()} centros médicos...");
            
            $resultados = [];
            foreach ($centros as $centro) {
                $tenant = Tenant::findOrCreateForCentro($centro);
                $tenant->makeCurrent();
                $resultadosCentro = $this->procesarCentro($centro, $medicosIds, $fechaInicio, $fechaFin);
                $resultados = array_merge($resultados, $resultadosCentro);
            }
        }
        
        // Generar PDF si se solicitó
        if ($this->option('descargar') && count($resultados) > 0) {
            $nombreArchivo = $this->generarPDFNomina($resultados, $fechaInicio, $fechaFin, $centroId);
            $this->info("PDF de nómina generado: {$nombreArchivo}");
        }
        
        $this->info('Generación de nómina completada.');
        return 0;
    }
    
    /**
     * Calcula automáticamente el periodo de la nómina
     */
    private function calcularPeriodoAutomatico($periodo, &$fechaInicio, &$fechaFin)
    {
        $ahora = Carbon::now();
        
        if ($periodo == 'quincenal') {
            // Si estamos en la primera quincena
            if ($ahora->day <= 15) {
                $fechaInicio = Carbon::create($ahora->year, $ahora->month, 1);
                $fechaFin = Carbon::create($ahora->year, $ahora->month, 15);
            } else {
                // Segunda quincena
                $fechaInicio = Carbon::create($ahora->year, $ahora->month, 16);
                $fechaFin = Carbon::create($ahora->year, $ahora->month)->endOfMonth();
            }
        } else {
            // Mensual
            $fechaInicio = Carbon::create($ahora->year, $ahora->month, 1);
            $fechaFin = Carbon::create($ahora->year, $ahora->month)->endOfMonth();
        }
    }
    
    /**
     * Procesa un centro médico específico
     */
    private function procesarCentro($centro, $medicosIds, $fechaInicio, $fechaFin)
    {
        $this->info("Procesando centro: {$centro->nombre_centro}");
        
        // Si se especificaron médicos, procesamos solo esos médicos
        if (!empty($medicosIds)) {
            $medicos = Medico::whereIn('id', $medicosIds)->get();
            if ($medicos->isEmpty()) {
                $this->warn("No se encontraron médicos con los IDs proporcionados.");
                return [];
            }
        } else {
            // Procesar todos los médicos del centro
            $medicos = Medico::where('centro_id', $centro->id)->get();
        }
        
        $this->info("Procesando {$medicos->count()} médicos del centro {$centro->nombre_centro}...");
        
        $resultados = [];
        foreach ($medicos as $medico) {
            $datosMedico = $this->procesarMedico($medico, $fechaInicio, $fechaFin, $centro);
            if ($datosMedico) {
                $resultados[] = $datosMedico;
            }
        }
        
        return $resultados;
    }
    
    /**
     * Procesa un médico específico
     */
    private function procesarMedico($medico, $fechaInicio, $fechaFin, $centro)
    {
        $nombreMedico = $medico->persona ? $medico->persona->nombre_completo : "Médico ID: {$medico->id}";
        $this->info("Procesando médico: Dr. {$nombreMedico}");
        
        // Buscar contrato actual del médico
        $contrato = ContratoMedico::where('medico_id', $medico->id)
            ->where('activo', 'SI')
            ->first();
            
        $porcentajeMedico = $contrato ? $contrato->porcentaje_servicio : config('contabilidad.porcentaje_medico_default', 80);
        
        // Buscar liquidaciones en el periodo
        $liquidaciones = LiquidacionHonorario::where('medico_id', $medico->id)
            ->where('centro_id', $centro->id)
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->get();
            
        $this->info("Se encontraron {$liquidaciones->count()} liquidaciones en el periodo.");
        
        if ($liquidaciones->isEmpty()) {
            $this->warn("No hay liquidaciones para el médico en este periodo.");
            return null;
        }
        
        // Buscar pagos realizados en el periodo
        $pagos = PagoHonorario::whereIn('liquidacion_id', $liquidaciones->pluck('id'))
            ->whereBetween('fecha_pago', [$fechaInicio, $fechaFin])
            ->get();
            
        // Calcular totales
        $totalLiquidaciones = $liquidaciones->sum('monto_total');
        $totalPagado = $pagos->sum('monto');
        $totalPendiente = $totalLiquidaciones - $totalPagado;
        
        // Calcular retenciones
        $totalRetenciones = $pagos->sum('retencion_isr_monto');
        $montoNeto = $totalPagado - $totalRetenciones;
        
        return [
            'medico' => $medico,
            'nombre_medico' => $nombreMedico,
            'especialidad' => $medico->especialidadPrincipal ? $medico->especialidadPrincipal->nombre : 'No definida',
            'centro' => $centro->nombre_centro,
            'periodo_inicio' => $fechaInicio->format('Y-m-d'),
            'periodo_fin' => $fechaFin->format('Y-m-d'),
            'liquidaciones' => $liquidaciones,
            'pagos' => $pagos,
            'porcentaje_medico' => $porcentajeMedico,
            'total_liquidaciones' => $totalLiquidaciones,
            'total_pagado' => $totalPagado,
            'total_pendiente' => $totalPendiente,
            'total_retenciones' => $totalRetenciones,
            'monto_neto' => $montoNeto,
            'contrato' => $contrato,
        ];
    }
    
    /**
     * Genera un PDF con la nómina
     */
    private function generarPDFNomina($resultados, $fechaInicio, $fechaFin, $centroId = null)
    {
        // Preparar datos para la vista
        $datos = [
            'resultados' => $resultados,
            'periodo_inicio' => $fechaInicio->format('d/m/Y'),
            'periodo_fin' => $fechaFin->format('d/m/Y'),
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i:s'),
            'total_general' => array_sum(array_column($resultados, 'total_pagado')),
            'total_retenciones' => array_sum(array_column($resultados, 'total_retenciones')),
            'total_neto' => array_sum(array_column($resultados, 'monto_neto')),
        ];
        
        // Generar PDF
        $pdf = PDF::loadView('pdf.nomina', $datos);
        
        // Guardar PDF
        $nombreArchivo = 'nomina_' . $fechaInicio->format('Ymd') . '_' . $fechaFin->format('Ymd');
        if ($centroId) {
            $nombreArchivo .= "_centro_{$centroId}";
        }
        $nombreArchivo .= '.pdf';
        
        $pdf->save(storage_path('app/public/nominas/' . $nombreArchivo));
        
        return $nombreArchivo;
    }
}
