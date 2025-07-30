@php
    $form = $this->form;
@endphp

<x-filament::page>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Configuración de Factura</h1>
        {{ $form }}
        <div class="mt-6 flex justify-center">
            <button type="button" wire:click="save" class="px-6 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700">
                Guardar Configuración
            </button>
        </div>
    </div>
</x-filament::page>
