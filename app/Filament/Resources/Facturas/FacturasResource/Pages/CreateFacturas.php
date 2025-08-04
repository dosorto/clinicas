<?php

namespace App\Filament\Resources\Facturas\FacturasResource\Pages;

use App\Filament\Resources\Facturas\FacturasResource;
use App\Models\{Consulta, FacturaDetalle, Descuento};
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CreateFacturas extends CreateRecord
{
    protected static string $resource = FacturasResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function beforeCreate(): void
    {
        Log::info('Creando factura', ['consulta_id' => $this->data['consulta_id'] ?? null]);
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            $record = parent::handleRecordCreation($data);
            Log::info('Factura creada exitosamente', ['factura_id' => $record->id]);
            return $record;
        } catch (\Exception $e) {
            Log::error('Error creando factura: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function onValidationError(ValidationException $exception): void
    {
        Log::error('=== ERROR DE VALIDACIÓN ===');
        Log::error('Errores de validación:', $exception->errors());
        Log::error('Datos del formulario:', $this->data);
        
        parent::onValidationError($exception);
    }

    /* ─────────────────────────  M O U N T  ───────────────────────── */
    public function mount(): void
    {
        parent::mount();

        $consultaId = request()->get('consulta_id');
        Log::info('=== MOUNT FACTURA ===', ['consulta_id' => $consultaId]);

        if (! $consultaId) {
            Log::info('No hay consulta_id en la URL');
            return;            // Abriste la ruta sin consulta ⇒ campos vacíos
        }

        $consulta = Consulta::with(['paciente.persona','medico.persona'])
                    ->find($consultaId);

        if (! $consulta) {
            Log::error('Consulta no encontrada', ['consulta_id' => $consultaId]);
            return;
        }

        Log::info('Consulta encontrada', [
            'consulta_id' => $consulta->id,
            'paciente_id' => $consulta->paciente_id,
            'medico_id' => $consulta->medico_id
        ]);

        /* 1) Traer los detalles sueltos para mostrar totales preliminares */
        $detalles = FacturaDetalle::where('consulta_id', $consultaId)
                    ->whereNull('factura_id')
                    ->with('servicio.impuesto')
                    ->get();

        $subtotal      = $detalles->sum('subtotal');
        $impuestoTotal = $detalles->sum('impuesto_monto');

        /* 2) Pre-rellenamos el formulario */
        $formData = [
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
        ];
        
        Log::info('Datos pre-llenados en el formulario:', $formData);
        $this->form->fill($formData);
    }

    /* ─────────── P R E  G U A R D A D O ─────────── */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Log::info('=== MUTATE FORM DATA BEFORE CREATE ===', $data);
        
        $consultaId = request()->get('consulta_id') ?? $data['consulta_id'] ?? null;
        if (!$consultaId) {
            Log::error('Falta consulta_id');
            throw ValidationException::withMessages(['consulta_id' => 'Falta la consulta.']);
        }

        $detalles = FacturaDetalle::where('consulta_id', $consultaId)
                    ->whereNull('factura_id')
                    ->with('servicio.impuesto')
                    ->get();

        if ($detalles->isEmpty()) {
            // Log para debug
            \Log::info('No hay servicios para facturar', ['consulta_id' => $consultaId]);
            throw ValidationException::withMessages(['subtotal' => 'No hay servicios para facturar en esta consulta.']);
        }

        // Log para debug
        \Log::info('Servicios encontrados para facturar', [
            'consulta_id' => $consultaId,
            'cantidad_servicios' => $detalles->count(),
            'subtotal' => $detalles->sum('subtotal'),
            'impuesto_total' => $detalles->sum('impuesto_monto')
        ]);

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
        $data['centro_id']   = Auth::user()->centro_id;
        $data['created_by']  = Auth::id();
        $data['fecha_emision'] ??= now()->toDateString();
        $data['estado']        ??= 'PENDIENTE';

        // Configuración CAI
        $data['usa_cai'] = $data['usa_cai'] ?? true;
        
        // Validar disponibilidad de CAI si se requiere
        if ($data['usa_cai']) {
            $cai = \App\Services\CaiNumerador::obtenerCAIDisponible($data['centro_id']);
            if (!$cai) {
                // Si no hay CAI disponible, forzar a no usar CAI
                $data['usa_cai'] = false;
                
                // Opcional: notificar al usuario
                \Filament\Notifications\Notification::make()
                    ->warning()
                    ->title('Sin CAI disponible')
                    ->body('No hay autorizaciones CAI activas. La factura se creará como proforma.')
                    ->send();
            }
        }

        return $data;
    }

    /* ─────────────────────  P O S T  G U A R D A D O ─────────────────── */
    protected function afterCreate(): void
    {
        Log::info('=== PROCESANDO FACTURA DESPUÉS DE CREAR ===', [
            'factura_id' => $this->record->id,
            'consulta_id' => $this->record->consulta_id
        ]);
        
        try {
            // 1. ASIGNAR DETALLES A LA FACTURA
            $consultaId = $this->record->consulta_id;
            
            // Buscar detalles pendientes de esta consulta
            $detallesPendientes = FacturaDetalle::where('consulta_id', $consultaId)
                                ->whereNull('factura_id')
                                ->get();
            
            Log::info('Detalles encontrados para asignar', [
                'cantidad' => $detallesPendientes->count(),
                'consulta_id' => $consultaId
            ]);
            
            // Asignar cada detalle a esta factura
            foreach ($detallesPendientes as $detalle) {
                $detalle->update(['factura_id' => $this->record->id]);
                Log::info('Detalle asignado', [
                    'detalle_id' => $detalle->id,
                    'servicio_id' => $detalle->servicio_id,
                    'factura_id' => $this->record->id
                ]);
            }
            
            // 2. REFRESCAR EL MODELO PARA OBTENER PAGOS Y DETALLES
            $this->record->refresh();
            
            // 3. ACTUALIZAR ESTADO Y CREAR CUENTA POR COBRAR SI ES NECESARIO
            $this->record->actualizarEstadoPago();
            
            Log::info('Factura procesada completamente', [
                'factura_id' => $this->record->id, 
                'estado_final' => $this->record->estado,
                'monto_pagado' => $this->record->montoPagado(),
                'saldo_pendiente' => $this->record->saldoPendiente(),
                'detalles_asignados' => $detallesPendientes->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error procesando factura después de crear: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }
}