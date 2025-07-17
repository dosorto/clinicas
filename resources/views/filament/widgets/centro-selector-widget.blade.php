<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                @php
                    $user = auth()->user();
                    $availableCentros = $user->getAccessibleCentros();
                    $selectedCentro = session('current_centro_id') ?? $user->centro_id;
                @endphp
                
                @if($availableCentros->count() > 1)
                    <div class="flex items-center space-x-3">
                        <label class="text-sm font-medium text-gray-700">Centro Médico:</label>
                        @foreach($availableCentros as $centro)
                            @if($centro->id == $selectedCentro)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    {{ $centro->nombre_centro }}
                                </span>
                            @else
                                <a 
                                    href="?switch_centro={{ $centro->id }}"
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors"
                                >
                                    {{ $centro->nombre_centro }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center space-x-2">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span class="text-sm font-medium text-gray-900">
                            {{ $availableCentros->first()?->nombre_centro ?? 'Sin centro asignado' }}
                        </span>
                    </div>
                @endif
            </div>
            
            @if(auth()->user()->hasRole('root'))
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    Administrador
                </span>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
