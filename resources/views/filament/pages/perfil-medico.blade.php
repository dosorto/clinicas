<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Información Personal -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                Información Personal
            </h3>
            {{ $this->form }}
        </div>

        <!-- Configuración del Recetario -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Configuración del Recetario
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Active su recetario médico para prescribir medicamentos
                    </p>
                </div>
                
                @if($this->recetarioData['tiene_recetario'])
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                        <span class="text-sm font-medium text-green-600 dark:text-green-400">
                            Recetario Activo
                        </span>
                    </div>
                @else
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                            Sin Recetario
                        </span>
                    </div>
                @endif
            </div>

            <div class="mt-4">
                <label class="inline-flex items-center">
                    <input 
                        type="checkbox" 
                        wire:model.live="recetarioData.tiene_recetario"
                        wire:change="cambiarRecetario"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600"
                    >
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                        Activar Recetario Médico
                    </span>
                </label>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Active esta opción para habilitar su recetario médico
                </p>
            </div>
        </div>

        <!-- Estadísticas del Recetario -->
        @if($this->recetarioData['tiene_recetario'] && auth()->user()->medico)
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Estadísticas
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                        <div class="flex items-center">
                            <x-heroicon-o-document-text class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                    Total Recetas
                                </p>
                                <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                                    {{ auth()->user()->medico->recetas()->count() }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                        <div class="flex items-center">
                            <x-heroicon-o-check-circle class="w-8 h-8 text-green-600 dark:text-green-400" />
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-600 dark:text-green-400">
                                    Este Mes
                                </p>
                                <p class="text-2xl font-bold text-green-900 dark:text-green-100">
                                    {{ auth()->user()->medico->recetas()->whereMonth('created_at', now()->month)->count() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(!auth()->user()->medico)
            <!-- Aviso para usuarios con rol médico pero sin registro -->
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-6">
                <div class="flex items-start">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-amber-600 dark:text-amber-400 mt-1" />
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-amber-900 dark:text-amber-100">
                            Registro de Médico Pendiente
                        </h3>
                        <div class="mt-2 text-sm text-amber-800 dark:text-amber-200">
                            <p>Usted tiene el rol de médico, pero aún no tiene un registro completo en el sistema.</p>
                            <p class="mt-2">Para activar su recetario y acceder a todas las funcionalidades médicas, debe contactar al administrador del sistema para completar su registro con:</p>
                            <ul class="list-disc list-inside mt-2 space-y-1">
                                <li>Número de colegiación</li>
                                <li>Horarios de trabajo</li>
                                <li>Especialidades médicas</li>
                                <li>Asignación a centros médicos</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Ayuda y Documentación -->
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-6">
            <div class="flex items-start">
                <x-heroicon-o-information-circle class="w-6 h-6 text-amber-600 dark:text-amber-400 mt-1" />
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-amber-900 dark:text-amber-100">
                        ¿Cómo usar su recetario?
                    </h3>
                    <div class="mt-2 text-sm text-amber-800 dark:text-amber-200">
                        <ol class="list-decimal list-inside space-y-1">
                            <li>Active su recetario usando el interruptor arriba</li>
                            <li>Configure las observaciones generales que aparecerán en todas sus recetas</li>
                            <li>Al crear una consulta, podrá generar recetas asociadas a este recetario</li>
                            <li>Cada receta tendrá un número correlativo único</li>
                            <li>Puede consultar el historial de recetas en la sección de reportes</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
