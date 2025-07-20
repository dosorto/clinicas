<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between w-full">
                <span>Centro Médico Actual</span>
                @if(auth()->user()->hasRole('root'))
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        Administrador
                    </span>
                @endif
            </div>
        </x-slot>

        <div class="space-y-4">
            @php
                $user = auth()->user();
                $availableCentros = $user->getAccessibleCentros();
                $selectedCentro = session('current_centro_id') ?? $user->centro_id;
                $currentCentro = $availableCentros->where('id', $selectedCentro)->first();
            @endphp

            @if($currentCentro)
                <div class="rounded-lg bg-success-50 p-4 border border-success-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="h-6 w-6 text-success-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <div>
                                <h3 class="text-lg font-semibold text-success-900">
                                    {{ $currentCentro->nombre_centro }}
                                </h3>
                                <p class="text-sm text-success-700">Centro médico activo</p>
                            </div>
                        </div>
                        
                        @if($availableCentros->count() > 1)
                            <svg class="h-5 w-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                            </svg>
                        @endif
                    </div>
                </div>
            @endif

            @if($availableCentros->count() > 1)
                <div>
                    <label for="centro-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Cambiar centro médico:
                    </label>
                    <div class="relative">
                        <select 
                            id="centro-select"
                            onchange="window.location.href = '?switch_centro=' + this.value"
                            class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-400 dark:focus:border-primary-400 sm:text-sm rounded-md shadow-sm"
                        >
                            <option value="" class="text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800">Seleccionar centro...</option>
                            @foreach($availableCentros as $centro)
                                @if($centro->id !== $selectedCentro)
                                    <option value="{{ $centro->id }}" class="text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-800">
                                        {{ $centro->nombre_centro }}
                                        @if($centro->direccion)
                                            - {{ Str::limit($centro->direccion, 50) }}
                                        @endif
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Tienes acceso a {{ $availableCentros->count() }} centros médicos
                    </div>
                </div>
            @elseif($availableCentros->count() === 1)
                <div class="text-center py-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Solo tienes acceso a un centro médico</p>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
