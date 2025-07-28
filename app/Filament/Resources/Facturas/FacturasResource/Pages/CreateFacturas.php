<?php
// filepath: c:\xampp\htdocs\Laravel\ProyectoClinica\clinicas\app\Filament\Resources\Facturas\FacturasResource\Pages\CreateFacturas.php

namespace App\Filament\Resources\Facturas\FacturasResource\Pages;

use App\Filament\Resources\Facturas\FacturasResource;
use App\Models\FacturaDetalle;
use App\Models\Consulta;
use App\Models\Servicio;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Filament\Forms\Get;

class CreateFacturas extends CreateRecord
{
    protected static string $resource = FacturasResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function mount(): void
    {
        parent::mount();
        
        // Obtener datos de la consulta desde la URL
        $consultaId = request()->get('consulta_id');
        $subtotalFromUrl = request()->get('subtotal');
        
        if ($consultaId) {
            $consulta = Consulta::with(['paciente.persona', 'medico.persona'])->find($consultaId);
            
            if ($consulta) {
                // Calcular totales automáticamente
                $serviciosData = $this->calcularTotalesConsulta($consultaId);
                
                // Pre-llenar el formulario
                $this->form->fill([
                    'consulta_id' => $consultaId,
                    'fecha_emision' => now()->format('Y-m-d'),
                    'estado' => 'PENDIENTE',
                    'subtotal' => $serviciosData['subtotal'],
                    'impuesto_total' => $serviciosData['impuesto_total'],
                    'descuento_total' => $serviciosData['descuento_total'],
                    'total' => $serviciosData['total'],
                ]);
            }
        }
    }

    protected function calcularTotalesConsulta(int $consultaId): array
    {
        // Obtener todos los servicios de la consulta
        $serviciosConsulta = FacturaDetalle::where('consulta_id', $consultaId)
            ->whereNull('factura_id')
            ->with('servicio.impuesto')
            ->get();

        $subtotal = 0;
        $impuestoTotal = 0;
        $descuentoTotal = 0;

        foreach ($serviciosConsulta as $detalle) {
            $servicio = $detalle->servicio;
            $cantidad = $detalle->cantidad;
            
            // Subtotal por servicio
            $subtotalServicio = $servicio->precio_unitario * $cantidad;
            $subtotal += $subtotalServicio;
            
            // Calcular impuesto si no está exonerado
            if ($servicio->es_exonerado === 'NO' && $servicio->impuesto) {
                $impuestoServicio = ($subtotalServicio * $servicio->impuesto->porcentaje) / 100;
                $impuestoTotal += $impuestoServicio;
            }
        }

        // Calcular descuento del paciente si existe
        $consulta = Consulta::with('paciente')->find($consultaId);
        if ($consulta && $consulta->paciente && isset($consulta->paciente->tipo_descuento)) {
            $descuentoTotal = $this->calcularDescuentoPaciente($subtotal, $consulta->paciente);
        }

        $total = $subtotal + $impuestoTotal - $descuentoTotal;

        return [
            'subtotal' => round($subtotal, 2),
            'impuesto_total' => round($impuestoTotal, 2),
            'descuento_total' => round($descuentoTotal, 2),
            'total' => round($total, 2),
        ];
    }

    protected function calcularDescuentoPaciente(float $subtotal, $paciente): float
    {
        if (!isset($paciente->tipo_descuento) || !isset($paciente->valor_descuento)) {
            return 0;
        }

        switch ($paciente->tipo_descuento) {
            case 'PORCENTAJE':
                return ($subtotal * $paciente->valor_descuento) / 100;
            case 'MONTO_FIJO':
                return min($paciente->valor_descuento, $subtotal); // No puede ser mayor al subtotal
            default:
                return 0;
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $consultaId = request()->get('consulta_id');
        
        if ($consultaId) {
            $consulta = Consulta::find($consultaId);
            if ($consulta) {
                $data['consulta_id'] = $consultaId;
                $data['paciente_id'] = $consulta->paciente_id;
                $data['medico_id'] = $consulta->medico_id;
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $consultaId = request()->get('consulta_id');
        
        if ($consultaId) {
            // Asociar los detalles de factura existentes con esta nueva factura
            FacturaDetalle::where('consulta_id', $consultaId)
                ->whereNull('factura_id')
                ->update(['factura_id' => $this->record->id]);

            // Recalcular totales exactos basados en los detalles
            $this->recalcularTotalesFactura();

            Notification::make()
                ->title('Factura creada exitosamente')
                ->body('Los servicios de la consulta han sido transferidos a la factura.')
                ->success()
                ->send();
        }
    }

    protected function recalcularTotalesFactura(): void
    {
        $detalles = FacturaDetalle::where('factura_id', $this->record->id)
            ->with('servicio.impuesto')
            ->get();

        $subtotal = 0;
        $impuestoTotal = 0;

        foreach ($detalles as $detalle) {
            $subtotal += $detalle->total_linea;
            
            // Calcular impuesto real por detalle
            $servicio = $detalle->servicio;
            if ($servicio->es_exonerado === 'NO' && $servicio->impuesto) {
                $impuestoDetalle = ($detalle->total_linea * $servicio->impuesto->porcentaje) / 100;
                $impuestoTotal += $impuestoDetalle;
                
                // Actualizar el impuesto en el detalle
                $detalle->update(['impuesto_monto' => round($impuestoDetalle, 2)]);
            }
        }

        $total = $subtotal + $impuestoTotal - $this->record->descuento_total;

        // Actualizar la factura con los totales calculados
        $this->record->update([
            'subtotal' => round($subtotal, 2),
            'impuesto_total' => round($impuestoTotal, 2),
            'total' => round($total, 2),
        ]);
    }
}