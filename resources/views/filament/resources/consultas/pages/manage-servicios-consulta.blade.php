{{-- filepath: c:\xampp\htdocs\Laravel\ProyectoClinica\clinicas\resources\views\filament\resources\consultas\pages\manage-servicios-consulta.blade.php --}}
<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Servicios de la Consulta
        </x-slot>
        
        <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <h3 class="font-semibold text-blue-900 dark:text-blue-100">
                Consulta: {{ $this->record->id }}
            </h3>
            <p class="text-blue-700 dark:text-blue-300">
                Paciente: {{ $this->record->paciente->persona->nombres }} {{ $this->record->paciente->persona->apellidos }}
            </p>
            <p class="text-blue-700 dark:text-blue-300">
                Médico: {{ $this->record->medico->persona->nombres }} {{ $this->record->medico->persona->apellidos }}
            </p>
        </div>

        {{ $this->table }}
        
        {{-- Mostrar subtotal general --}}
        @php
            $total = $this->getServiciosTotal();
            $cantidad = $this->getCantidadServicios();
        @endphp
        
        @if($total > 0)
        <div class="mt-6 p-6 bg-gradient-to-r from-green-50 to-blue-50 dark:from-green-900/20 dark:to-blue-900/20 rounded-lg border border-green-200 dark:border-green-700">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                <div class="text-center md:text-left">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Servicios agregados</p>
                    <p class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                        {{ $cantidad }} servicio(s)
                    </p>
                </div>
                
                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Estado</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                        📋 Pendiente de facturar
                    </span>
                </div>
                
                <div class="text-center md:text-right">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Subtotal General</p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                        L. {{ number_format($total, 2) }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Suma de todos los servicios
                    </p>
                </div>
            </div>
            
            <div class="mt-4 pt-4 border-t border-green-200 dark:border-green-700">
                <p class="text-xs text-green-700 dark:text-green-300 text-center">
                    💡 Este subtotal se transferirá automáticamente a la factura cuando continúes con la facturación
                </p>
            </div>
        </div>
        @else
        <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-900/20 rounded-lg border border-gray-200 dark:border-gray-700 text-center">
            <p class="text-gray-600 dark:text-gray-400">
                ℹ️ No hay servicios agregados aún. Use el botón "Agregar Servicios" para comenzar.
            </p>
        </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>