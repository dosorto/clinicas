<x-filament-panels::page>
    <div class="bg-white p-6 rounded-lg shadow-lg" id="receipt-content">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-2xl font-bold text-primary-600">RECIBO DE PAGO</h2>
                <p class="text-gray-500">Comprobante de Pago de Honorarios Médicos</p>
            </div>
            <div class="text-right">
                <p class="font-semibold">Recibo #{{ $record->id }}</p>
                <p>Fecha: {{ $record->fecha_pago->format('d/m/Y') }}</p>
            </div>
        </div>
        
        <hr class="my-4 border-gray-200">
        
        <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
                <h3 class="font-semibold text-gray-700 mb-2">Información del Médico</h3>
                <p class="font-bold text-gray-900">{{ $record->liquidacion->medico->persona->nombre_completo }}</p>
                <p>{{ $record->liquidacion->medico->especialidad ?? 'Médico General' }}</p>
                <p>{{ $record->liquidacion->medico->num_colegiado ?? 'No. Colegiado no disponible' }}</p>
            </div>
            <div>
                <h3 class="font-semibold text-gray-700 mb-2">Información del Centro</h3>
                <p class="font-bold text-gray-900">{{ $record->centro->nombre_centro }}</p>
                <p>{{ $record->centro->direccion ?? 'Dirección no disponible' }}</p>
                <p>{{ $record->centro->telefono ?? 'Teléfono no disponible' }}</p>
            </div>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <h3 class="font-semibold text-gray-700 mb-2">Resumen del Pago</h3>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-600">Cargo Médico</p>
                    <p class="font-bold">
                        #{{ $record->liquidacion_id }} - Servicios médicos del mes de {{ $record->fecha_pago->format('F Y') }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-600">Monto a Pagar</p>
                    <p class="font-bold">L. {{ number_format($record->monto_pagado, 2) }}</p>
                </div>
            </div>
            
            <hr class="my-3 border-gray-200">
            
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-gray-600">Método de Pago</p>
                    <p class="font-bold">{{ ucfirst($record->metodo_pago) }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Referencia</p>
                    <p class="font-bold">{{ $record->referencia_bancaria ?: 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Estado</p>
                    <p class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Pagado
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <h3 class="font-semibold text-gray-700 mb-2">Desglose del Pago</h3>
            
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="py-2">Concepto</th>
                        <th class="py-2 text-right">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-200">
                        <td class="py-2">Monto bruto</td>
                        <td class="py-2 text-right">L. {{ number_format($record->monto_pagado, 2) }}</td>
                    </tr>
                    @if($record->retencion_isr_monto > 0)
                    <tr class="border-b border-gray-200">
                        <td class="py-2">Retención ISR ({{ number_format($record->retencion_isr_pct, 2) }}%)</td>
                        <td class="py-2 text-right">- L. {{ number_format($record->retencion_isr_monto, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="font-bold">
                        <td class="py-2">TOTAL NETO</td>
                        <td class="py-2 text-right">L. {{ number_format($record->monto_pagado - $record->retencion_isr_monto, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        @if($record->observaciones)
        <div class="mb-6">
            <h3 class="font-semibold text-gray-700 mb-2">Observaciones</h3>
            <p class="text-gray-600">{{ $record->observaciones }}</p>
        </div>
        @endif
        
        <div class="mt-8 pt-8 border-t border-gray-200">
            <div class="flex justify-between">
                <div class="text-center">
                    <div class="border-t border-gray-400 pt-1 w-48 mx-auto">
                        Firma del Médico
                    </div>
                </div>
                <div class="text-center">
                    <div class="border-t border-gray-400 pt-1 w-48 mx-auto">
                        Sello y Firma del Centro
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-8 text-center text-gray-500 text-sm">
            <p>Este documento es un comprobante válido de pago de honorarios médicos.</p>
        </div>
    </div>
    
    <script>
        document.addEventListener('print-receipt', () => {
            // Guardar el contenido actual de la página
            const originalContent = document.body.innerHTML;
            
            // Reemplazar el contenido con solo el recibo
            const receiptContent = document.getElementById('receipt-content').innerHTML;
            document.body.innerHTML = `
                <div style="padding: 20px;">
                    ${receiptContent}
                </div>
            `;
            
            // Imprimir
            window.print();
            
            // Restaurar el contenido original
            document.body.innerHTML = originalContent;
        });
    </script>
</x-filament-panels::page>
