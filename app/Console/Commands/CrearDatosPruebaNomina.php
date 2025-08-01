<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Medico;
use App\Models\ContabilidadMedica\ContratoMedico;
use App\Models\ContabilidadMedica\LiquidacionHonorario;
use App\Models\ContabilidadMedica\PagoHonorario;
use Carbon\Carbon;

class CrearDatosPruebaNomina extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nomina:crear-datos-prueba';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear datos de prueba para la nómina médica';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏥 Creando datos de prueba para nómina médica...');
        
        // Obtener médicos existentes
        $medicos = Medico::with('persona')->take(3)->get();
        
        if ($medicos->count() == 0) {
            $this->error('❌ No hay médicos en la base de datos. Crea médicos primero.');
            return;
        }
        
        $this->info("📋 Encontrados {$medicos->count()} médicos");
        
        foreach ($medicos as $medico) {
            $nombreMedico = $medico->persona ? $medico->persona->nombre_completo : 'Médico #' . $medico->id;
            $this->info("👨‍⚕️ Procesando: {$nombreMedico}");
            
            // 1. Crear contrato si no existe
            $contrato = ContratoMedico::where('medico_id', $medico->id)->first();
            if (!$contrato) {
                $contrato = ContratoMedico::create([
                    'medico_id' => $medico->id,
                    'salario_quincenal' => rand(15000, 25000),
                    'salario_mensual' => rand(30000, 50000),
                    'porcentaje_servicio' => rand(30, 60),
                    'fecha_inicio' => Carbon::now()->subMonths(6),
                    'fecha_fin' => null,
                    'activo' => 'SI',
                    'centro_id' => $medico->centro_id ?? 1,
                    'created_by' => 1,
                ]);
                $this->info("   ✅ Contrato creado: Salario L.{$contrato->salario_mensual}, {$contrato->porcentaje_servicio}%");
            }
            
            // 2. Crear liquidaciones del mes actual
            $existeLiquidacion = LiquidacionHonorario::where('medico_id', $medico->id)
                ->where('periodo_inicio', '>=', Carbon::now()->startOfMonth())
                ->exists();
                
            if (!$existeLiquidacion) {
                for ($i = 1; $i <= rand(2, 4); $i++) {
                    $monto = rand(5000, 15000);
                    $liquidacion = LiquidacionHonorario::create([
                        'medico_id' => $medico->id,
                        'contrato_medico_id' => $contrato->id,
                        'periodo_inicio' => Carbon::now()->startOfMonth()->addDays($i * 7),
                        'periodo_fin' => Carbon::now()->startOfMonth()->addDays(($i * 7) + 6),
                        'servicios_brutos' => $monto,
                        'porcentaje_medico' => $contrato->porcentaje_servicio,
                        'monto_total' => $monto * ($contrato->porcentaje_servicio / 100),
                        'deducciones' => 0,
                        'estado' => rand(0, 1) ? 'PAGADO' : 'PENDIENTE',
                        'fecha_liquidacion' => Carbon::now()->subDays(rand(1, 10)),
                        'observaciones' => "Liquidación semana {$i} - Servicios médicos",
                        'created_by' => 1,
                    ]);
                    
                    // 3. Crear pago para liquidaciones pagadas
                    if ($liquidacion->estado === 'PAGADO') {
                        $retencion = $liquidacion->monto_total * 0.15;
                        PagoHonorario::create([
                            'liquidacion_id' => $liquidacion->id,
                            'fecha_pago' => Carbon::now()->subDays(rand(1, 5)),
                            'monto_pagado' => $liquidacion->monto_total,
                            'retencion_isr_porcentaje' => 15,
                            'retencion_isr_monto' => $retencion,
                            'monto_neto' => $liquidacion->monto_total - $retencion,
                            'metodo_pago' => 'transferencia',
                            'referencia_pago' => 'TRF-' . rand(100000, 999999),
                            'observaciones' => 'Pago automático - datos de prueba',
                            'created_by' => 1,
                        ]);
                    }
                }
                $this->info("   💰 Liquidaciones creadas para el mes actual");
            } else {
                $this->info("   ⚠️  Ya tiene liquidaciones este mes");
            }
        }
        
        $this->info('🎉 Datos de prueba creados exitosamente!');
        $this->info('');
        $this->info('💡 Ahora puedes:');
        $this->info('   1. Ir a "Nómina Médica" en el panel admin');
        $this->info('   2. Generar PDFs que contendrán estos datos');
        $this->info('   3. Ver el "Historial de Nóminas" con las liquidaciones');
        
        return 0;
    }
}
