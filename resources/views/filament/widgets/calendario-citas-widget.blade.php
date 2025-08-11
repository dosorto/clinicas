{{-- resources/views/filament/widgets/calendario-citas-widget.blade.php --}}
<x-filament-widgets::widget>
    {{-- 1. Encabezado --------------------------------------------------------- --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                📅 Calendario de Citas
            </h2>
        </div>
    </x-slot>

    {{-- 2. Calendario --------------------------------------------------------- --}}
    <div
        id="calendario-widget-{{ $this->getId() }}"
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
    >
        {{-- Barra superior --}}
        <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <button wire:click="mesAnterior" class="p-2 rounded-full hover:bg-white dark:hover:bg-gray-700 transition-all duration-200 shadow-sm hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <h2 class="text-xl font-bold capitalize text-gray-900 dark:text-white bg-white dark:bg-gray-800 px-4 py-2 rounded-lg shadow-sm">
                        {{ $mesActual }} {{ $anio }}
                    </h2>

                    <button wire:click="mesSiguiente" class="p-2 rounded-full hover:bg-white dark:hover:bg-gray-700 transition-all duration-200 shadow-sm hover:shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <button wire:click="irHoy" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow-md flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Hoy
                </button>
            </div>
        </div>

        {{-- 2.1 Cuerpo del calendario --}}
        <div class="p-4">
            {{-- Días de la semana --}}
            <div class="grid grid-cols-7 gap-px mb-3">
                @foreach (['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $d)
                    <div class="py-3 text-center font-semibold text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 rounded-lg">{{ $d }}</div>
                @endforeach
            </div>

            {{-- Días del mes --}}
            <div class="grid grid-cols-7 gap-px">
                @php
                    $primerDia  = Carbon\Carbon::createFromDate($anio,$mes,1);
                    $ultimoDia  = Carbon\Carbon::createFromDate($anio,$mes,1)->endOfMonth();
                    $diasVacios = ($primerDia->dayOfWeek - 1 + 7) % 7;   // lunes = 0
                    $totalDias  = $ultimoDia->day;
                    $diasRest   = 42 - ($diasVacios + $totalDias);      // 6 filas × 7 columnas
                @endphp

                {{-- huecos antes --}}
                @for ($i=0;$i<$diasVacios;$i++)
                    <div class="h-32 p-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-lg opacity-60"></div>
                @endfor

                {{-- días reales --}}
                @for ($dia=1; $dia<= $totalDias; $dia++)
                    @php
                        $hoy          = Carbon\Carbon::now();
                        $esHoy        = $hoy->day==$dia && $hoy->month==$mes && $hoy->year==$anio;
                        $tieneCitas   = !empty($citasPorDia[$dia]);
                        $citasCount   = $tieneCitas ? count($citasPorDia[$dia]) : 0;
                    @endphp

                    <div
                        wire:click="mostrarCitasDelDia('{{ $dia }}')"
                        class="dia-calendario h-32 p-2 border rounded-lg transition-all duration-200 transform hover:scale-105
                               {{ $esHoy ? 'border-blue-500 dark:border-blue-400 bg-blue-50 dark:bg-blue-900/30 shadow-lg' : 'border-gray-200 dark:border-gray-700' }}
                               {{ $tieneCitas ? 'bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 hover:from-green-100 hover:to-emerald-100' : 'bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700' }}
                               overflow-hidden cursor-pointer shadow-sm hover:shadow-md"
                    >
                        <div class="flex justify-between items-start mb-1">
                            <div class="flex items-center justify-center w-7 h-7 rounded-full font-bold text-sm
                                        {{ $esHoy ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-md'
                                                  : ($tieneCitas ? 'bg-gradient-to-r from-green-500 to-emerald-500 text-white'
                                                                 : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600') }}">
                                {{ $dia }}
                            </div>

                            @if ($tieneCitas)
                                <div class="flex items-center space-x-1">
                                    <span class="text-xs font-bold px-2 py-1 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-full shadow-sm flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $citasCount }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        @if ($tieneCitas)
                            <div class="space-y-1.5 max-h-20 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300">
                                @foreach (array_slice($citasPorDia[$dia],0,3) as $index => $cita)
                                    <div class="p-1.5 text-xs rounded-lg transform transition-all duration-150 hover:scale-102 cursor-pointer"
                                         style="background:{{ $cita['color'] }}20; border-left: 3px solid {{ $cita['color'] }};">
                                        <div
                                            class="cita-preview block"
                                            wire:click.stop="mostrarCitasDelDia('{{ $dia }}', {{ $cita['id'] }})"
                                        >
                                            <div class="flex items-center justify-between">
                                                <span class="font-semibold text-gray-800 dark:text-gray-100 flex items-center">
                                                    ⏰ {{ $cita['hora'] }}
                                                </span>
                                                @if($cita['estado'] === 'Confirmado')
                                                    <span class="text-green-600">✅</span>
                                                @elseif($cita['estado'] === 'Pendiente')
                                                    <span class="text-yellow-600">⏳</span>
                                                @elseif($cita['estado'] === 'Cancelado')
                                                    <span class="text-red-600">❌</span>
                                                @else
                                                    <span class="text-blue-600">✨</span>
                                                @endif
                                            </div>
                                            <div class="mt-1 text-gray-600 dark:text-gray-300 truncate font-medium">
                                                👤 {{ $cita['paciente'] }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @if (count($citasPorDia[$dia])>3)
                                    <div class="text-xs text-center font-medium px-2 py-1 bg-gradient-to-r from-blue-100 to-indigo-100 dark:from-blue-800 dark:to-indigo-800 text-blue-700 dark:text-blue-200 rounded-lg shadow-sm">
                                        📋 +{{ count($citasPorDia[$dia])-3 }} citas más
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endfor

                {{-- huecos después --}}
                @for ($i=0;$i<$diasRest;$i++)
                    <div class="h-32 p-2 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-lg opacity-60"></div>
                @endfor
            </div>
        </div>
    </div>

    {{-- 3. Incluir la modal de citas del día --}}
    @include('filament.widgets.modal-citas-del-dia')
    
    {{-- 5. Estilos extra (mínimos) --}}
    <style>
        .dia-calendario{transition:all .2s}
        .dia-calendario:hover{box-shadow:inset 0 0 0 2px rgba(59,130,246,.5)}
        .cita-preview{cursor:pointer;transition:opacity .2s}
        .cita-preview:hover{opacity:.8}
    </style>
</x-filament-widgets::widget>