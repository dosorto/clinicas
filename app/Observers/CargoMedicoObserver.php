<?php

namespace App\Observers;

use App\Models\ContabilidadMedica\CargoMedico;
use App\Models\ContabilidadMedica\LiquidacionHonorario;
use App\Models\ContabilidadMedica\ContratoMedico;
use Illuminate\Support\Facades\Log;

class CargoMedicoObserver
{
    /**
     * Handle the CargoMedico "created" event.
     */
    public function created(CargoMedico $cargoMedico): void
    {
        // Generar liquidación automáticamente si está configurado en las opciones
        if (config('contabilidad.liquidacion_automatica', false)) {
            $this->generarLiquidacion($cargoMedico);
        }
    }

    /**
     * Handle the CargoMedico "updated" event.
     */
    public function updated(CargoMedico $cargoMedico): void
    {
        // Si el cargo cambió a "aprobado" y está configurado, generar liquidación
        if ($cargoMedico->isDirty('estado') && 
            $cargoMedico->estado === 'aprobado' && 
            config('contabilidad.liquidacion_automatica_aprobacion', true)) {
            $this->generarLiquidacion($cargoMedico);
        }
    }

    /**
     * Genera una liquidación de honorarios automáticamente
     */
    private function generarLiquidacion(CargoMedico $cargoMedico): void
    {
        try {
            // Verificar si ya existe una liquidación para este cargo
            if ($cargoMedico->liquidacion()->exists()) {
                Log::info("Ya existe una liquidación para el cargo médico #{$cargoMedico->id}. No se creará una nueva.");
                return;
            }

            // Buscar el contrato del médico para obtener los porcentajes
            $contrato = ContratoMedico::where('medico_id', $cargoMedico->medico_id)
                ->where('estado', 'activo')
                ->first();

            // Usar porcentaje por defecto si no hay contrato
            $porcentajeMedico = $contrato ? $contrato->porcentaje_medico : 80;
            $porcentajeCentro = 100 - $porcentajeMedico;

            // Calcular montos
            $montoTotal = $cargoMedico->total;
            $montoCentro = $montoTotal * ($porcentajeCentro / 100);
            $montoMedico = $montoTotal * ($porcentajeMedico / 100);

            // Crear la liquidación
            $liquidacion = new LiquidacionHonorario();
            $liquidacion->medico_id = $cargoMedico->medico_id;
            $liquidacion->cargo_medico_id = $cargoMedico->id;
            $liquidacion->centro_id = $cargoMedico->centro_id;
            $liquidacion->periodo_inicio = $cargoMedico->periodo_inicio;
            $liquidacion->periodo_fin = $cargoMedico->periodo_fin;
            $liquidacion->monto_total = $montoTotal;
            $liquidacion->porcentaje_centro = $porcentajeCentro;
            $liquidacion->monto_centro = $montoCentro;
            $liquidacion->porcentaje_medico = $porcentajeMedico;
            $liquidacion->monto_total = $montoMedico;
            $liquidacion->estado = 'pendiente';
            $liquidacion->tipo_liquidacion = 'honorarios';
            
            $liquidacion->save();

            Log::info("Liquidación #{$liquidacion->id} generada automáticamente para el cargo médico #{$cargoMedico->id}");
        } catch (\Exception $e) {
            Log::error("Error al generar liquidación para el cargo médico #{$cargoMedico->id}: " . $e->getMessage());
        }
    }
}
