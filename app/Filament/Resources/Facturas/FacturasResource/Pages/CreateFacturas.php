<?php

namespace App\Filament\Resources\Facturas\FacturasResource\Pages;

use App\Filament\Resources\Facturas\FacturasResource;
use App\Models\{Consulta, FacturaDetalle, Descuento};
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateFacturas extends CreateRecord
{
    protected static string $resource = FacturasResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /* ─────────────────────────  M O U N T  ───────────────────────── */
    public function mount(): void
    {
        parent::mount();

        $consultaId = request()->get('consulta_id');

        if (! $consultaId) {
            return;            // Abriste la ruta sin consulta ⇒ campos vacíos
        }

        $consulta = Consulta::with(['paciente.persona','medico.persona'])
                    ->find($consultaId);

        if (! $consulta) {
            return;
        }

        /* 1) Traer los detalles sueltos para mostrar totales preliminares */
        $detalles = FacturaDetalle::where('consulta_id', $consultaId)
                    ->whereNull('factura_id')
                    ->with('servicio.impuesto')
                    ->get();

        $subtotal      = $detalles->sum('subtotal');
        $impuestoTotal = $detalles->sum('impuesto_monto');

        /* 2) Pre-rellenamos el formulario */
        $this->form->fill([
            'consulta_id'   => $consultaId,
            'paciente_id'   => $consulta->paciente_id,
            'medico_id'     => $consulta->medico_id,
            'fecha_emision' => now()->format('Y-m-d'),
            'estado'        => 'PENDIENTE',
            'subtotal'      => round($subtotal,2),
            'impuesto_total'=> round($impuestoTotal,2),
            'descuento_id'  => null,
            'descuento_total'=> 0,
            'total'         => round($subtotal + $impuestoTotal,2),
        ]);
    }

    /* ─────────── P R E  G U A R D A D O ─────────── */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $consultaId = request()->get('consulta_id') ?? $data['consulta_id'] ?? null;
        if (!$consultaId) {
            throw ValidationException::withMessages(['consulta_id' => 'Falta la consulta.']);
        }

        $detalles = FacturaDetalle::where('consulta_id', $consultaId)
                    ->whereNull('factura_id')
                    ->with('servicio.impuesto')
                    ->get();

        if ($detalles->isEmpty()) {
            throw ValidationException::withMessages(['subtotal' => 'No hay servicios para facturar.']);
        }

        $subtotal      = $detalles->sum('subtotal');
        $impuestoTotal = $detalles->sum('impuesto_monto');

        $descuentoTotal = 0;
        if (!empty($data['descuento_id'])) {
            $descuento = Descuento::find($data['descuento_id']);
            if ($descuento) {
                $descuentoTotal = $descuento->tipo === 'PORCENTAJE'
                    ? $subtotal * $descuento->valor / 100
                    : min($descuento->valor, $subtotal);
            }
        }

        if (!empty($data['impuesto_id'])) {
            $impuestoPct   = \App\Models\Impuesto::find($data['impuesto_id'])?->porcentaje ?? 0;
            $impuestoTotal = round($subtotal * $impuestoPct / 100, 2);
        }

        $consulta = Consulta::findOrFail($consultaId);

        /* Totales */
        $data['subtotal']        = round($subtotal,2);
        $data['impuesto_total']  = round($impuestoTotal,2);
        $data['descuento_total'] = round($descuentoTotal,2);
        $data['total']           = round($subtotal + $impuestoTotal - $descuentoTotal,2);

        /* Datos requeridos */
        $data['consulta_id'] = $consulta->id;
        $data['paciente_id'] = $consulta->paciente_id;
        $data['medico_id']   = $consulta->medico_id;
        $data['cita_id']     = $consulta->cita_id;
        $data['centro_id']   = auth()->user()->centro_id;
        $data['created_by']  = auth()->id();
        $data['fecha_emision'] ??= now()->toDateString();
        $data['estado']        ??= 'PENDIENTE';

        return $data;
    }

    /* ─────────────────────  P O S T  G U A R D A D O ─────────────────── */
    public function afterCreate(): void
    {
        // Enlazar detalles…
        \App\Models\FacturaDetalle::where('consulta_id', $this->record->consulta_id)
            ->whereNull('factura_id')
            ->update(['factura_id' => $this->record->id]);

        // Registrar pagos (CORREGIDO - removido montoDevolucion)
        foreach ($this->data['pagos'] ?? [] as $pago) {
            \App\Services\FacturaPagoService::registrarPago(
                factura       : $this->record,
                montoRecibido : $pago['monto_recibido'],
                tipoPagoId    : $pago['tipo_pago_id'],
                usuarioId     : auth()->id(),
            );
        }

        parent::afterCreate();
    }
}