<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            <!-- Header del widget -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-document-text class="w-8 h-8 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            Recetario Médico
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Configure su recetario para prescribir medicamentos
                        </p>
                    </div>
                </div>
                
                <!-- Estado visual -->
                @if($this->data['tiene_recetario'])
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                        <span class="text-sm font-medium text-green-600 dark:text-green-400">
                            Activo
                        </span>
                    </div>
                @else
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            Inactivo
                        </span>
                    </div>
                @endif
            </div>

            <!-- Formulario -->
            <form wire:submit.prevent class="space-y-4">
                {{ $this->form }}
            </form>

            <!-- Información adicional para usuarios sin registro -->
            @if(!auth()->user()->medico)
                <div class="mt-4 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                    <div class="flex items-start space-x-3">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0" />
                        <div class="text-sm text-amber-800 dark:text-amber-200">
                            <p class="font-medium">Registro de médico pendiente</p>
                            <p class="mt-1">Para activar su recetario, debe completar su registro médico en el sistema.</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Estadísticas rápidas -->
            @if($this->data['tiene_recetario'] && auth()->user()->medico)
                <div class="mt-4 grid grid-cols-2 gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                                {{ auth()->user()->medico->recetas()->count() }}
                            </p>
                            <p class="text-xs text-blue-600 dark:text-blue-400">
                                Total Recetas
                            </p>
                        </div>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-green-900 dark:text-green-100">
                                {{ auth()->user()->medico->recetas()->whereMonth('created_at', now()->month)->count() }}
                            </p>
                            <p class="text-xs text-green-600 dark:text-green-400">
                                Este Mes
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
