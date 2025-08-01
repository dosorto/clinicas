<x-filament-panels::page>
    <div class="p-4 bg-white rounded-xl shadow">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-primary-600">Recibo de Pago de Honorarios</h1>
            <p class="text-gray-500">Sistema de Contabilidad Médica</p>
        </div>
        
        <!-- Información principal del recibo -->
        <div class="flex justify-between items-start mb-6">
            <div class="rounded-lg border border-gray-300 p-3 bg-gray-50 w-1/2 mr-2">
                <h3 class="font-semibold text-gray-700">Información del Pago</h3>
                <div class="grid grid-cols-2 gap-2 mt-2">
                    <div class="text-sm text-gray-500">Recibo No:</div>
                    <div class="text-sm font-medium">{{ str_pad($pago->id, 6, '0', STR_PAD_LEFT) }}</div>
                    
                    <div class="text-sm text-gray-500">Fecha de Pago:</div>
                    <div class="text-sm font-medium">{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</div>
                    
                    <div class="text-sm text-gray-500">Método de Pago:</div>
                    <div class="text-sm font-medium">{{ ucfirst($pago->metodo_pago) }}</div>
                    
                    @if($pago->referencia_pago)
                    <div class="text-sm text-gray-500">Referencia:</div>
                    <div class="text-sm font-medium">{{ $pago->referencia_pago }}</div>
                    @endif
                    
                    <div class="text-sm text-gray-500">Estado:</div>
                    <div class="text-sm font-medium">
                        @if($pago->estado == 'completado')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Completado
                            </span>
                        @elseif($pago->estado == 'parcial')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Parcial
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ ucfirst($pago->estado) }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="rounded-lg border border-gray-300 p-3 bg-gray-50 w-1/2 ml-2">
                <h3 class="font-semibold text-gray-700">Información del Médico</h3>
                <div class="grid grid-cols-2 gap-2 mt-2">
                    <div class="text-sm text-gray-500">Médico:</div>
                    <div class="text-sm font-medium">
                        {{ $pago->liquidacion->cargoMedico->medico->persona->nombre_completo ?? 'N/A' }}
                    </div>
                    
                    <div class="text-sm text-gray-500">Centro:</div>
                    <div class="text-sm font-medium">
                        {{ $pago->liquidacion->cargoMedico->centro->nombre_centro ?? 'N/A' }}
                    </div>
                    
                    <div class="text-sm text-gray-500">Cargo ID:</div>
                    <div class="text-sm font-medium">
                        {{ $pago->liquidacion->cargo_medico_id ?? 'N/A' }}
                    </div>
                    
                    <div class="text-sm text-gray-500">Liquidación:</div>
                    <div class="text-sm font-medium">
                        #{{ $pago->liquidacion_id }}
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Detalles del monto -->
        <div class="mb-6">
            <div class="rounded-lg border border-gray-300 p-4 bg-primary-50">
                <h3 class="font-semibold text-primary-700 mb-2">Detalle del Pago</h3>
                
                <div class="grid grid-cols-2 gap-4 mb-2">
                    <div>
                        <p class="text-sm text-gray-500">Concepto:</p>
                        <p class="font-medium">{{ $pago->concepto }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Periodo:</p>
                        <p class="font-medium">
                            @if($pago->liquidacion->cargoMedico)
                                {{ \Carbon\Carbon::parse($pago->liquidacion->cargoMedico->periodo_inicio)->format('d/m/Y') }} - 
                                {{ \Carbon\Carbon::parse($pago->liquidacion->cargoMedico->periodo_fin)->format('d/m/Y') }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
                
                <div class="flex justify-between items-center mt-4 bg-white p-3 rounded-lg border border-primary-200">
                    <div>
                        <p class="text-lg font-bold text-primary-700">Monto Total:</p>
                        <p class="text-sm text-gray-500">
                            {{ ucfirst($this->numeroALetras($pago->monto)) }}
                        </p>
                    </div>
                    <div class="text-2xl font-bold text-primary-700">
                        L. {{ number_format($pago->monto, 2) }}
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Información adicional -->
        @if($pago->observaciones)
        <div class="mb-6">
            <div class="rounded-lg border border-gray-300 p-3 bg-gray-50">
                <h3 class="font-semibold text-gray-700 mb-1">Observaciones</h3>
                <p class="text-sm">{{ $pago->observaciones }}</p>
            </div>
        </div>
        @endif
        
        <!-- Firmas -->
        <div class="mt-10 grid grid-cols-2 gap-10">
            <div class="text-center">
                <div class="border-t border-gray-300 pt-2 w-48 mx-auto"></div>
                <p class="text-sm text-gray-600">Firma del Pagador</p>
            </div>
            <div class="text-center">
                <div class="border-t border-gray-300 pt-2 w-48 mx-auto"></div>
                <p class="text-sm text-gray-600">Firma del Médico</p>
            </div>
        </div>
        
        <!-- Pie de página -->
        <div class="mt-10 pt-4 border-t border-gray-200 text-center text-xs text-gray-500">
            <p>Este recibo fue generado automáticamente el {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>Documento sin validez fiscal - Solo para control interno</p>
        </div>
    </div>
</x-filament-panels::page>
