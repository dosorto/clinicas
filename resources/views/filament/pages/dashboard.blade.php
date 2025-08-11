<x-filament-panels::page>
    {{-- Header personalizado --}}
    <div class="mb-6 bg-gradient-to-r from-blue-600 via-purple-600 to-emerald-600 rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">{{ $this->getHeading() }}</h1>
                <p class="text-blue-100 text-sm">{{ $this->getSubheading() }}</p>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold">{{ now()->format('H:i') }}</div>
                <div class="text-sm text-blue-100">Hora actual</div>
            </div>
        </div>
        
        {{-- Accesos rápidos --}}
        <div class="mt-4 flex gap-3 flex-wrap">
            <a href="/admin/citas/citas/create" 
               class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-all duration-200 backdrop-blur-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nueva Cita
            </a>
            <a href="/admin/pacientes/create" 
               class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-all duration-200 backdrop-blur-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
                Nuevo Paciente
            </a>
            @if(auth()->user()->hasRole('medico'))
            <a href="/admin/consultas/consultas/create" 
               class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-all duration-200 backdrop-blur-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Nueva Consulta
            </a>
            @endif
        </div>
    </div>

    {{-- Widgets del dashboard --}}
    <div class="space-y-6">
        @foreach ($this->getWidgets() as $widget)
            @livewire(\Livewire\Livewire::getAlias($widget), ['lazy' => true])
        @endforeach
    </div>

    {{-- Información adicional al pie --}}
    <div class="mt-8 bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
            <div class="flex items-center space-x-4">
                <span>🩺 Sistema de Gestión Médica</span>
                <span>•</span>
                <span>Versión 2.0</span>
            </div>
            <div>
                Última actualización: {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    <style>
        /* Animaciones suaves */
        .fi-wi-stats-overview-stat {
            transition: all 0.3s ease;
        }
        .fi-wi-stats-overview-stat:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</x-filament-panels::page>
