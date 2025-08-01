<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medico;
use App\Models\Centros_Medico;
use App\Models\ContabilidadMedica\CargoMedico;
use App\Models\ContabilidadMedica\ContratoMedico;
use App\Models\ContabilidadMedica\LiquidacionHonorario;
use App\Models\ContabilidadMedica\PagoHonorario;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContabilidadMedicaSeeder extends Seeder
{
    public function run()
    {
        // Verificar si ya existen datos
        if (CargoMedico::count() > 0) {
            $this->command->info('Ya existen datos de contabilidad médica. Saltando...');
            return;
        }

        $this->command->info('Creando datos de prueba para Contabilidad Médica...');

        // Asegurarse que hay centros médicos y médicos
        $centro = Centros_Medico::first();
        if (!$centro) {
            $this->command->error('No hay centros médicos. Por favor, crea uno primero.');
            return;
        }

        $medicos = Medico::take(3)->get();
        if ($medicos->isEmpty()) {
            $this->command->error('No hay médicos. Por favor, crea al menos uno primero.');
            return;
        }

        DB::beginTransaction();
        try {
            // 1. Crear Contratos Médicos
            $this->command->info('Creando contratos médicos...');
            $contratos = [];
            
            foreach ($medicos as $index => $medico) {
                $contrato = ContratoMedico::create([
                    'medico_id' => $medico->id,
                    'centro_id' => $centro->id,
                    'fecha_inicio' => Carbon::now()->subDays(60),
                    'fecha_fin' => Carbon::now()->addDays(365),
                    'salario_quincenal' => 5000 + ($index * 1000),
                    'salario_mensual' => 10000 + ($index * 2000),
                    'porcentaje_servicio' => 70 + ($index * 5),
                    'activo' => 'SI',
                ]);
                
                $contratos[] = $contrato;
                $this->command->info("- Creado contrato #{$contrato->id} para médico #{$medico->id}");
            }

            // 2. Crear Cargos Médicos
            $this->command->info('Creando cargos médicos...');
            $cargos = [];
            
            foreach ($medicos as $index => $medico) {
                $cargo = CargoMedico::create([
                    'medico_id' => $medico->id,
                    'centro_id' => $centro->id,
                    'contrato_id' => $contratos[$index]->id,
                    'descripcion' => 'Consulta ' . ($index + 1) . ' - Honorarios médicos',
                    'periodo_inicio' => Carbon::now()->subDays(30),
                    'periodo_fin' => Carbon::now(),
                    'subtotal' => 1000 * ($index + 1),
                    'impuesto_total' => 150 * ($index + 1),
                    'total' => 1150 * ($index + 1),
                    'estado' => 'pendiente',
                    'observaciones' => 'Cargo de prueba para demo',
                ]);
                
                $cargos[] = $cargo;
                $this->command->info("- Creado cargo médico #{$cargo->id} por L. {$cargo->total}");
            }
            
            // 3. Crear Liquidaciones de Honorarios
            $this->command->info('Creando liquidaciones de honorarios...');
            $liquidaciones = [];
            
            foreach ($cargos as $cargo) {
                $liquidacion = LiquidacionHonorario::create([
                    'medico_id' => $cargo->medico_id,
                    'centro_id' => $centro->id,
                    'periodo_inicio' => $cargo->periodo_inicio,
                    'periodo_fin' => $cargo->periodo_fin,
                    'monto_total' => $cargo->total,
                    'estado' => 'PENDIENTE',
                    'tipo_liquidacion' => 'PORCENTAJE',
                ]);
                
                $liquidaciones[] = $liquidacion;
                $this->command->info("- Creada liquidación #{$liquidacion->id} por L. {$liquidacion->monto_total}");
            }
            
            // 4. Crear algunos pagos
            $this->command->info('Creando pagos de honorarios...');
            
            if (isset($liquidaciones[0])) {
                $pago = PagoHonorario::create([
                    'liquidacion_id' => $liquidaciones[0]->id,
                    'centro_id' => $centro->id,
                    'fecha_pago' => Carbon::now(),
                    'monto_pagado' => $liquidaciones[0]->monto_total,
                    'metodo_pago' => 'TRANSFERENCIA',
                    'referencia_bancaria' => 'TRANS-001',
                    'retencion_isr_pct' => 10,
                    'retencion_isr_monto' => $liquidaciones[0]->monto_total * 0.1,
                    'observaciones' => 'Pago completo de prueba',
                ]);
                
                $liquidaciones[0]->estado = 'PAGADA';
                $liquidaciones[0]->save();
                
                $cargos[0]->estado = 'pagado';
                $cargos[0]->save();
                
                $this->command->info("- Creado pago #{$pago->id} por L. {$pago->monto_pagado}");
            }
            
            DB::commit();
            $this->command->info('¡Datos de prueba para ContabilidadMedica creados exitosamente!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Error al crear datos de prueba: " . $e->getMessage());
            $this->command->error("Línea: " . $e->getLine());
        }
    }
}
