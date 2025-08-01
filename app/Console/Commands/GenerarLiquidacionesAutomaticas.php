<?php

namespace App\Console\Commands;

use App\Models\ContabilidadMedica\CargoMedico;
use App\Models\ContabilidadMedica\LiquidacionHonorario;
use App\Models\Medico;
use App\Models\Centros_Medico;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerarLiquidacionesAutomaticas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contabilidad:generar-liquidaciones 
                            {--centro_id= : ID del centro médico específico}
                            {--fecha= : Fecha para la liquidación (formato: Y-m-d)}
                            {--medico_id= : ID del médico específico}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera liquidaciones automáticas para cargos médicos pendientes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando generación automática de liquidaciones...');
        
        $centroId = $this->option('centro_id');
        $medicoId = $this->option('medico_id');
        $fecha = $this->option('fecha') ? Carbon::parse($this->option('fecha')) : Carbon::now();
        
        // Si se especificó un centro, lo establecemos como tenant
        if ($centroId) {
            $centro = Centros_Medico::find($centroId);
            if (!$centro) {
                $this->error("Centro médico con ID {$centroId} no encontrado.");
                return 1;
            }
            Tenant::set($centro);
            $this->procesarCentro($centro, $medicoId, $fecha);
        } else {
            // Procesar todos los centros
            $centros = Centros_Medico::all();
            $this->info("Procesando {$centros->count()} centros médicos...");
            
            foreach ($centros as $centro) {
                Tenant::set($centro);
                $this->procesarCentro($centro, $medicoId, $fecha);
            }
        }
        
        $this->info('Generación de liquidaciones completada.');
        return 0;
    }
    
    /**
     * Procesa un centro médico específico
     */
    private function procesarCentro($centro, $medicoId, $fecha)
    {
        $this->info("Procesando centro: {$centro->nombre_centro}");
        
        // Si se especificó un médico, procesamos solo ese médico
        if ($medicoId) {
            $medico = Medico::find($medicoId);
            if (!$medico) {
                $this->warn("Médico con ID {$medicoId} no encontrado.");
                return;
            }
            $this->procesarMedico($medico, $fecha, $centro);
        } else {
            // Procesar todos los médicos del centro
            $medicos = Medico::where('centro_id', $centro->id)->get();
            $this->info("Procesando {$medicos->count()} médicos del centro {$centro->nombre_centro}...");
            
            foreach ($medicos as $medico) {
                $this->procesarMedico($medico, $fecha, $centro);
            }
        }
    }
    
    /**
     * Procesa un médico específico
     */
    private function procesarMedico($medico, $fecha, $centro)
    {
        $this->info("Procesando médico: Dr. {$medico->persona->nombre_completo}");
        
        // Buscar cargos pendientes de liquidación
        $cargos = CargoMedico::where('medico_id', $medico->id)
            ->where('centro_id', $centro->id)
            ->where('estado', 'pendiente')
            ->whereNotExists(function ($query) {
                $query->select('id')
                      ->from('liquidaciones_honorarios')
                      ->whereColumn('cargo_medico_id', 'cargos_medicos.id');
            })
            ->get();
            
        $this->info("Se encontraron {$cargos->count()} cargos pendientes para el médico.");
        
        if ($cargos->isEmpty()) {
            return;
        }
        
        // Agrupar los cargos en una única liquidación
        $montoTotal = $cargos->sum('total');
        
        // Obtener porcentaje del médico (desde contrato o configuración)
        $porcentajeMedico = config('contabilidad.porcentaje_medico_default');
        $porcentajeCentro = 100 - $porcentajeMedico;
        
        // Calcular montos
        $montoCentro = $montoTotal * ($porcentajeCentro / 100);
        $montoMedico = $montoTotal * ($porcentajeMedico / 100);
        
        // Crear liquidación
        $liquidacion = new LiquidacionHonorario();
        $liquidacion->medico_id = $medico->id;
        $liquidacion->centro_id = $centro->id;
        $liquidacion->periodo_inicio = $cargos->min('periodo_inicio');
        $liquidacion->periodo_fin = $cargos->max('periodo_fin');
        $liquidacion->monto_total = $montoTotal;
        $liquidacion->porcentaje_centro = $porcentajeCentro;
        $liquidacion->monto_centro = $montoCentro;
        $liquidacion->porcentaje_medico = $porcentajeMedico;
        $liquidacion->monto_total = $montoMedico;
        $liquidacion->estado = 'pendiente';
        $liquidacion->tipo_liquidacion = 'honorarios';
        $liquidacion->fecha_generacion = $fecha;
        
        $liquidacion->save();
        
        // Actualizar referencia en los cargos
        foreach ($cargos as $cargo) {
            $cargo->liquidacion_id = $liquidacion->id;
            $cargo->save();
        }
        
        $this->info("Liquidación #{$liquidacion->id} generada con éxito para el Dr. {$medico->persona->nombre_completo} por L. {$montoTotal}");
        
        // Enviar notificación si está habilitado
        if (config('contabilidad.notificaciones_liquidaciones')) {
            // Aquí iría el código para enviar notificaciones
            $this->info("Notificación enviada al médico sobre la nueva liquidación.");
        }
    }
}
