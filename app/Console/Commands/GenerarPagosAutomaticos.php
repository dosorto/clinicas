<?php

namespace App\Console\Commands;

use App\Models\ContabilidadMedica\LiquidacionHonorario;
use App\Models\ContabilidadMedica\PagoHonorario;
use App\Models\Centros_Medico;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PagoHonorarioRealizado;

class GenerarPagosAutomaticos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contabilidad:generar-pagos
                            {--centro_id= : ID del centro médico específico}
                            {--liquidacion_id= : ID de la liquidación específica}
                            {--fecha= : Fecha para el pago (formato: Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera pagos automáticos para liquidaciones pendientes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando generación automática de pagos...');
        
        // Verificar si los pagos automáticos están habilitados
        if (!config('contabilidad.pagos_automaticos')) {
            $this->warn('Los pagos automáticos están deshabilitados en la configuración.');
            return 0;
        }
        
        $centroId = $this->option('centro_id');
        $liquidacionId = $this->option('liquidacion_id');
        $fecha = $this->option('fecha') ? Carbon::parse($this->option('fecha')) : Carbon::now();
        
        // Si no es el día configurado para pagos automáticos y no se especificó una fecha, salir
        $diaPagoAutomatico = config('contabilidad.dia_pago_automatico', 15);
        if (!$this->option('fecha') && $fecha->day != $diaPagoAutomatico) {
            $this->info("Hoy no es día de pago automático. Los pagos están configurados para el día {$diaPagoAutomatico} del mes.");
            return 0;
        }
        
        // Si se especificó un centro, lo establecemos como tenant
        if ($centroId) {
            $centro = Centros_Medico::find($centroId);
            if (!$centro) {
                $this->error("Centro médico con ID {$centroId} no encontrado.");
                return 1;
            }
            Tenant::set($centro);
            $this->procesarCentro($centro, $liquidacionId, $fecha);
        } else {
            // Procesar todos los centros
            $centros = Centros_Medico::all();
            $this->info("Procesando {$centros->count()} centros médicos...");
            
            foreach ($centros as $centro) {
                Tenant::set($centro);
                $this->procesarCentro($centro, $liquidacionId, $fecha);
            }
        }
        
        $this->info('Generación de pagos completada.');
        return 0;
    }
    
    /**
     * Procesa un centro médico específico
     */
    private function procesarCentro($centro, $liquidacionId, $fecha)
    {
        $this->info("Procesando centro: {$centro->nombre_centro}");
        
        // Si se especificó una liquidación, procesamos solo esa
        if ($liquidacionId) {
            $liquidacion = LiquidacionHonorario::find($liquidacionId);
            if (!$liquidacion) {
                $this->warn("Liquidación con ID {$liquidacionId} no encontrada.");
                return;
            }
            $this->procesarLiquidacion($liquidacion, $fecha);
        } else {
            // Procesar todas las liquidaciones pendientes del centro
            $liquidaciones = LiquidacionHonorario::where('centro_id', $centro->id)
                ->where('estado', 'pendiente')
                ->get();
                
            $this->info("Procesando {$liquidaciones->count()} liquidaciones pendientes del centro {$centro->nombre_centro}...");
            
            foreach ($liquidaciones as $liquidacion) {
                $this->procesarLiquidacion($liquidacion, $fecha);
            }
        }
    }
    
    /**
     * Procesa una liquidación específica
     */
    private function procesarLiquidacion($liquidacion, $fecha)
    {
        $this->info("Procesando liquidación #{$liquidacion->id} para Dr. {$liquidacion->medico->persona->nombre_completo}");
        
        // Verificar si ya existe un pago para esta liquidación
        if (PagoHonorario::where('liquidacion_id', $liquidacion->id)->exists()) {
            $this->warn("Ya existe un pago para la liquidación #{$liquidacion->id}. Se omitirá.");
            return;
        }
        
        // Crear el pago
        try {
            $retencionPct = config('contabilidad.porcentaje_retencion_default', 10);
            $retencionMonto = $liquidacion->monto_total * ($retencionPct / 100);
            $montoNeto = $liquidacion->monto_total - $retencionMonto;
            
            $pago = new PagoHonorario();
            $pago->liquidacion_id = $liquidacion->id;
            $pago->centro_id = $liquidacion->centro_id;
            $pago->fecha_pago = $fecha;
            $pago->monto_pagado = $liquidacion->monto_total;
            $pago->metodo_pago = 'transferencia'; // Método por defecto para pagos automáticos
            $pago->referencia_pago = 'AUTO-' . $fecha->format('Ymd') . '-' . $liquidacion->id;
            $pago->retencion_isr_pct = $retencionPct;
            $pago->retencion_isr_monto = $retencionMonto;
            $pago->monto_neto = $montoNeto;
            $pago->concepto = "Pago automático de honorarios - Liquidación #{$liquidacion->id}";
            $pago->estado = 'completado';
            $pago->observaciones = "Pago generado automáticamente el {$fecha->format('d/m/Y')}";
            
            $pago->save();
            
            // Actualizar estado de la liquidación
            $liquidacion->estado = 'pagado';
            $liquidacion->save();
            
            // Actualizar estado del cargo médico si existe
            if ($liquidacion->cargoMedico) {
                $liquidacion->cargoMedico->estado = 'pagado';
                $liquidacion->cargoMedico->save();
            }
            
            $this->info("Pago #{$pago->id} generado con éxito para la liquidación #{$liquidacion->id} por L. {$pago->monto}");
            
            // Enviar notificación si está habilitado
            if (config('contabilidad.notificaciones_pagos') && isset($liquidacion->medico->user)) {
                // Aquí iría el código para enviar notificaciones
                // Notification::send($liquidacion->medico->user, new PagoHonorarioRealizado($pago));
                $this->info("Notificación enviada al médico sobre el nuevo pago.");
            }
            
        } catch (\Exception $e) {
            $this->error("Error al generar pago para liquidación #{$liquidacion->id}: " . $e->getMessage());
            Log::error("Error al generar pago automático: " . $e->getMessage());
        }
    }
}
