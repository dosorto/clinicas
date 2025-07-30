
{{-- filepath: c:\xampp\htdocs\Laravel\ProyectoClinica\clinicas\resources\views\filament\resources\consultas\pages\manage-servicios-consulta.blade.php --}}

<x-filament-panels::page>
    {{-- 1️⃣ tu tabla --}}
    {{ $this->table }}

    {{-- 2️⃣ panel de resumen --}}
    @php($subtotal = $this->getServiciosTotal())
    @php($cantidad = $this->getCantidadServicios())

    @if ($subtotal > 0)
        <x-filament::section class="mt-6">
            <x-slot name="heading">
                Resumen de Servicios
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                <div class="text-center md:text-left">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Servicios agregados
                    </p>
                    <p class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                        {{ $cantidad }} servicio(s)
                    </p>
                </div>

                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Estado</p>
                    <x-filament::badge color="warning">
                        📋 Pendiente de facturar
                    </x-filament::badge>
                </div>

                <div class="text-center md:text-right">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Subtotal General
                    </p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                        L. {{ number_format($subtotal, 2) }}
                    </p>
                </div>
            </div>
        </x-filament::section>
    @else
        <x-filament::section class="mt-6">
            <div class="text-center py-8">
                <div class="text-gray-400 text-6xl mb-4">
                    📋
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                    No hay servicios agregados
                </h3>
                <p class="text-gray-600 dark:text-gray-400">
                    Usa el botón <strong>"Agregar Servicios"</strong> para comenzar.
                </p>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>