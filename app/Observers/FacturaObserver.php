<?php

namespace App\Observers;

use App\Models\Factura;
use App\Models\ContabilidadMedica\CargoMedico;
use App\Models\ContabilidadMedica\ContratoMedico;
use Illuminate\Support\Facades\Log;

class FacturaObserver
{
    /**
     * Handle the Factura "created" event.
     */
    public function created(Factura $factura): void
    {
        // No hacemos nada en la creación, solo cuando cambia a pagada
    }

    /**
     * Handle the Factura "updated" event.
     */
    public function updated(Factura $factura): void
    {
        // Verificar si el estado cambió a "pagado"
        if ($factura->isDirty('estado') && $factura->estado === 'pagado') {
            $this->generarCargoMedico($factura);
        }
    }

    /**
     * Genera un cargo médico automáticamente a partir de una factura pagada
     */
    private function generarCargoMedico(Factura $factura): void
    {
        try {
            // Verificar si ya existe un cargo médico para esta factura
            if ($factura->cargosMedicos()->exists()) {
                Log::info("Ya existe un cargo médico para la factura #{$factura->id}. No se creará uno nuevo.");
                return;
            }

            // Verificar que tenga médico asignado
            if (!$factura->medico_id) {
                Log::warning("La factura #{$factura->id} no tiene médico asignado. No se puede generar cargo médico.");
                return;
            }

            // Crear el cargo médico
            $cargo = new CargoMedico();
            $cargo->medico_id = $factura->medico_id;
            $cargo->centro_id = $factura->centro_id;
            $cargo->factura_id = $factura->id;
            $cargo->descripcion = "Servicios médicos - Factura #{$factura->numero_factura}";
            $cargo->periodo_inicio = $factura->fecha;
            $cargo->periodo_fin = $factura->fecha;
            $cargo->subtotal = $factura->subtotal;
            $cargo->impuesto_total = $factura->impuesto;
            $cargo->total = $factura->total;
            $cargo->estado = 'pendiente';
            $cargo->observaciones = "Cargo generado automáticamente a partir de la factura #{$factura->numero_factura}";
            
            $cargo->save();

            Log::info("Cargo médico #{$cargo->id} generado automáticamente para la factura #{$factura->id}");
        } catch (\Exception $e) {
            Log::error("Error al generar cargo médico para la factura #{$factura->id}: " . $e->getMessage());
        }
    }
}
