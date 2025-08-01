@php
    $label = $getLabel();
@endphp

<x-filament::button
    :form="$getForm()"
    type="submit"
    size="lg"
    :wire:click="'submit'"
    class="filament-wizard-submit-button"
>
    {{ $label ?? 'Crear Cargo Médico' }}
</x-filament::button>
