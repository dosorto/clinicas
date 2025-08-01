<div class="space-y-6">
    <!-- Información del Médico -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <h3 class="font-semibold text-blue-900 dark:text-blue-100 mb-3">
            👨‍⚕️ Información del Médico
        </h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Nombre:</span>
                <p class="text-blue-900 dark:text-blue-100">
                    {{ $medico?->persona?->nombre_completo ?? 'Sin información' }}
                </p>
            </div>
            <div>
                <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Centro Médico:</span>
                <p class="text-blue-900 dark:text-blue-100">
                    {{ $contrato?->centro?->nombre_centro ?? 'Sin asignar' }}
                </p>
            </div>
            <div>
                <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Especialidad:</span>
                <p class="text-blue-900 dark:text-blue-100">
                    {{ $medico?->especialidades?->first()?->especialidad ?? 'General' }}
                </p>
            </div>
            <div>
                <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Estado del Contrato:</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $contrato?->activo === 'SI' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $contrato?->activo === 'SI' ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Período de Liquidación -->
    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
        <h3 class="font-semibold text-yellow-900 dark:text-yellow-100 mb-3">
            📅 Período de Liquidación
        </h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <span class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Fecha Inicio:</span>
                <p class="text-yellow-900 dark:text-yellow-100">
                    {{ \Carbon\Carbon::parse($liquidacion->periodo_inicio)->format('d/m/Y') }}
                </p>
            </div>
            <div>
                <span class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Fecha Fin:</span>
                <p class="text-yellow-900 dark:text-yellow-100">
                    {{ \Carbon\Carbon::parse($liquidacion->periodo_fin)->format('d/m/Y') }}
                </p>
            </div>
            <div>
                <span class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Días:</span>
                <p class="text-yellow-900 dark:text-yellow-100">
                    {{ \Carbon\Carbon::parse($liquidacion->periodo_inicio)->diffInDays(\Carbon\Carbon::parse($liquidacion->periodo_fin)) + 1 }} días
                </p>
            </div>
        </div>
    </div>

    <!-- Desglose Financiero -->
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
        <h3 class="font-semibold text-green-900 dark:text-green-100 mb-3">
            💰 Desglose Financiero
        </h3>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-green-800 dark:text-green-200">Salario Base:</span>
                <span class="font-mono text-green-900 dark:text-green-100">
                    L. {{ number_format($contrato?->salario_mensual ?? 0, 2) }}
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-green-800 dark:text-green-200">Servicios Brutos:</span>
                <span class="font-mono text-green-900 dark:text-green-100">
                    L. {{ number_format($liquidacion->servicios_brutos ?? 0, 2) }}
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-green-800 dark:text-green-200">Porcentaje del Médico:</span>
                <span class="font-mono text-green-900 dark:text-green-100">
                    {{ number_format($contrato?->porcentaje_servicio ?? 0, 1) }}%
                </span>
            </div>
            <hr class="border-green-200 dark:border-green-700">
            <div class="flex justify-between items-center">
                <span class="text-green-800 dark:text-green-200">Subtotal:</span>
                <span class="font-mono text-green-900 dark:text-green-100">
                    L. {{ number_format(($liquidacion->servicios_brutos ?? 0) + ($contrato?->salario_mensual ?? 0), 2) }}
                </span>
            </div>
            <div class="flex justify-between items-center text-red-600">
                <span>(-) Deducciones:</span>
                <span class="font-mono">
                    L. {{ number_format($liquidacion->deducciones ?? 0, 2) }}
                </span>
            </div>
            <hr class="border-green-300 dark:border-green-600">
            <div class="flex justify-between items-center text-lg font-bold">
                <span class="text-green-800 dark:text-green-200">Total Neto:</span>
                <span class="font-mono text-green-900 dark:text-green-100">
                    L. {{ number_format($liquidacion->monto_total ?? 0, 2) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Estado y Observaciones -->
    <div class="bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800 rounded-lg p-4">
        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">
            📋 Estado y Observaciones
        </h3>
        <div class="space-y-2">
            <div class="flex items-center space-x-2">
                <span class="text-gray-700 dark:text-gray-300">Estado:</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    @if($liquidacion->estado === 'PAGADO') bg-green-100 text-green-800
                    @elseif($liquidacion->estado === 'PENDIENTE') bg-yellow-100 text-yellow-800
                    @elseif($liquidacion->estado === 'PROCESANDO') bg-blue-100 text-blue-800
                    @else bg-gray-100 text-gray-800 @endif">
                    {{ $liquidacion->estado ?? 'Sin estado' }}
                </span>
            </div>
            <div>
                <span class="text-gray-700 dark:text-gray-300">Fecha de Liquidación:</span>
                <span class="text-gray-900 dark:text-gray-100 ml-2">
                    {{ $liquidacion->fecha_liquidacion ? \Carbon\Carbon::parse($liquidacion->fecha_liquidacion)->format('d/m/Y H:i') : 'Pendiente' }}
                </span>
            </div>
            @if($liquidacion->observaciones)
            <div>
                <span class="text-gray-700 dark:text-gray-300">Observaciones:</span>
                <p class="text-gray-900 dark:text-gray-100 mt-1 text-sm bg-white dark:bg-gray-800 p-2 rounded border">
                    {{ $liquidacion->observaciones }}
                </p>
            </div>
            @endif
        </div>
    </div>

    <!-- Información de Auditoría -->
    <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
        <div>Creado: {{ $liquidacion->created_at ? $liquidacion->created_at->format('d/m/Y H:i') : 'Sin fecha' }}</div>
        @if($liquidacion->updated_at && $liquidacion->updated_at->ne($liquidacion->created_at))
        <div>Última actualización: {{ $liquidacion->updated_at->format('d/m/Y H:i') }}</div>
        @endif
    </div>
</div>
