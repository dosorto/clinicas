<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Formulario principal --}}
        <form wire:submit="create" class="space-y-6">
            {{ $this->form }}

            {{-- Sección de médicos --}}
            <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Médicos en Nómina
                    </h3>
                </div>

                {{-- Botones de acción justo arriba de la tabla --}}
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex space-x-3 justify-end">
                        <button 
                            type="button"
                            wire:click="toggleSeleccionTodos"
                            class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition-all duration-200 shadow-md"
                        >
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                                </svg>
                                Seleccionar todos
                            </span>
                        </button>
                        <button 
                            type="button"
                            wire:click="deseleccionarTodos"
                            class="px-4 py-2 bg-orange-600 text-white font-medium rounded-lg hover:bg-orange-700 focus:ring-4 focus:ring-orange-300 transition-all duration-200 shadow-md"
                        >
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Deseleccionar todos
                            </span>
                        </button>
                    </div>
                </div>                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800">
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                                        Seleccionar
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                                        Médico
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                                        Salario
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                                        Deducciones
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                                        Percepciones
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                                        Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($medicosSeleccionados as $index => $medico)
                                    @if(isset($medico['nombre']) && !empty($medico['nombre']) && $medico['nombre'] !== 'Sin nombre')
                                    <tr class="transition-colors duration-150 hover:bg-blue-50 dark:hover:bg-gray-700">
                                        <td class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                            <input 
                                                type="checkbox" 
                                                wire:model="medicosSeleccionados.{{ $index }}.seleccionado"
                                                class="h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                                            >
                                        </td>
                                        <td class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                            <div class="font-medium text-gray-900 dark:text-white">
                                                {{ $medico['nombre'] }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                            <input 
                                                type="number" 
                                                step="0.01"
                                                wire:model.live="medicosSeleccionados.{{ $index }}.salario_base"
                                                class="w-full px-3 py-1 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                                                placeholder="0.00"
                                            >
                                        </td>
                                        <td class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                            <input 
                                                type="number" 
                                                step="0.01"
                                                wire:model.live="medicosSeleccionados.{{ $index }}.deducciones"
                                                class="w-full px-3 py-1 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                                                placeholder="0.00"
                                            >
                                        </td>
                                        <td class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                            <input 
                                                type="number" 
                                                step="0.01"
                                                wire:model.live="medicosSeleccionados.{{ $index }}.percepciones"
                                                class="w-full px-3 py-1 text-sm border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                                                placeholder="0.00"
                                            >
                                        </td>
                                        <td class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                            <span class="font-semibold text-green-600 dark:text-green-400">
                                                L. {{ number_format($medico['total'], 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Botón de guardar prominente --}}
            <div class="flex justify-center pt-6">
                <button 
                    type="submit"
                    class="px-8 py-3 bg-green-600 text-white font-bold text-lg rounded-lg hover:bg-green-700 focus:ring-4 focus:ring-green-300 transition-all duration-200 shadow-xl transform hover:scale-105"
                >
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        GUARDAR NÓMINA
                    </span>
                </button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
