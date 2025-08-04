<x-filament-widgets::widget>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium tracking-tight">Calendario de Citas</h2>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden" 
          id="calendario-widget-{{ $this->getId() }}">
        <!-- Cabecera del calendario -->
        <div class="p-4 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <button type="button" wire:click="mesAnterior" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white capitalize">{{ $mesActual }} {{ $anio }}</h2>
                    <button type="button" wire:click="mesSiguiente" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
                <div>
                    <button type="button" wire:click="hoy" class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                        Hoy
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Cuerpo del calendario -->
        <div class="p-4">
            <!-- Días de la semana -->
            <div class="grid grid-cols-7 gap-px mb-2">
                @php
                    $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                @endphp
                
                @foreach($diasSemana as $dia)
                    <div class="py-2 text-center font-medium text-gray-700 dark:text-gray-300">
                        {{ $dia }}
                    </div>
                @endforeach
            </div>
            
            <!-- Días del mes -->
            <div class="grid grid-cols-7 gap-px">
                @php
                    $primerDia = Carbon\Carbon::createFromDate($anio, $mes, 1);
                    $ultimoDia = Carbon\Carbon::createFromDate($anio, $mes, 1)->endOfMonth();
                    
                    // Ajustar para que el primer día de la semana sea lunes (1)
                    $diasVacios = ($primerDia->dayOfWeek - 1) % 7;
                    if ($diasVacios < 0) $diasVacios += 7;
                    
                    $totalDias = $ultimoDia->day;
                @endphp
                
                <!-- Celdas vacías antes del primer día -->
                @for($i = 0; $i < $diasVacios; $i++)
                    <div class="h-24 p-1 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800"></div>
                @endfor
                
                <!-- Días del mes -->
                @for($dia = 1; $dia <= $totalDias; $dia++)
                    @php
                        $esDiaActual = Carbon\Carbon::now()->day == $dia && 
                                      Carbon\Carbon::now()->month == $mes && 
                                      Carbon\Carbon::now()->year == $anio;
                        
                        $tieneCitas = isset($citasPorDia[$dia]) && count($citasPorDia[$dia]) > 0;
                    @endphp
                    
                    <div class="h-32 p-1 border dia-calendario {{ $esDiaActual ? 'border-primary-500 dark:border-primary-500' : 'border-gray-200 dark:border-gray-700' }} 
                              {{ $tieneCitas ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-white dark:bg-gray-800' }} 
                              overflow-hidden cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"
                         data-dia="{{ $dia }}">
                        <div class="flex justify-between">
                            <!-- Número del día -->
                            <div class="w-6 h-6 flex items-center justify-center {{ $esDiaActual ? 'bg-primary-500 text-white rounded-full' : 'text-gray-700 dark:text-gray-300' }}">
                                {{ $dia }}
                            </div>
                            
                            <!-- Indicador de cantidad de citas -->
                            @if($tieneCitas)
                                <div class="text-xs font-medium px-1.5 py-0.5 bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-100 rounded-full">
                                    {{ count($citasPorDia[$dia]) }}
                                </div>
                            @endif
                        </div>
                        
                        <!-- Listado de citas para este día -->
                        @if($tieneCitas)
                            <div class="mt-1 space-y-1 max-h-24 overflow-y-auto">
                                @foreach(array_slice($citasPorDia[$dia], 0, 3) as $cita)
                                    <div class="p-1 text-xs rounded-md" style="background-color: {{ $cita['color'] }}20; border-left: 3px solid {{ $cita['color'] }};">
                                        <div class="block hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded cita-preview" 
                                             data-cita-id="{{ $cita['id'] }}">
                                            <div class="font-medium text-gray-800 dark:text-gray-200 truncate">
                                                {{ $cita['hora'] }} - {{ $cita['paciente'] }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                
                                @if(count($citasPorDia[$dia]) > 3)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 text-center">
                                        +{{ count($citasPorDia[$dia]) - 3 }} más
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endfor
                
                <!-- Celdas vacías después del último día -->
                @php
                    $diasRestantes = 42 - ($diasVacios + $totalDias);
                @endphp
                
                @for($i = 0; $i < $diasRestantes; $i++)
                    <div class="h-24 p-1 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800"></div>
                @endfor
            </div>
        </div>
    </div>

    <!-- Modal de citas -->
    <div 
        x-data="{ 
            showModal: false, 
            dia: null, 
            citas: [],
            actualizarEstadoCita(citaId, nuevoEstado) {
                // Encontrar la cita en el arreglo y actualizar su estado
                const citaIndex = this.citas.findIndex(c => c.id === citaId);
                if (citaIndex !== -1) {
                    this.citas[citaIndex].estado = nuevoEstado;
                }
            }
        }"
        @mostrar-citas-dia.window="
            console.log('Evento recibido para mostrar modal', $event.detail);
            showModal = true;
            dia = $event.detail.dia;
            citas = $event.detail.citas;
        "
        id="modal-citas-{{ $this->getId() }}"
    >
        <div 
            x-show="showModal" 
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div 
                class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] overflow-hidden"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                @click.away="showModal = false"
            >
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Citas del día <span x-text="dia"></span>
                    </h3>
                    <button @click="showModal = false" type="button" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Cerrar</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="px-4 py-2 max-h-[60vh] overflow-y-auto">
                    <template x-if="citas.length === 0">
                        <div class="py-8 text-center text-gray-500 dark:text-gray-400">
                            No hay citas programadas para este día.
                        </div>
                    </template>
                    
                    <template x-for="(cita, index) in citas" :key="index">
                        <div class="py-3 border-b border-gray-200 dark:border-gray-700 last:border-0">
                            <div class="flex justify-between items-start space-x-4">
                                <div>
                                    <div class="flex items-center">
                                        <span x-text="cita.hora" class="font-medium mr-2"></span>
                                        <span x-text="cita.paciente" class="font-medium text-gray-800 dark:text-gray-200"></span>
                                    </div>
                                    <p x-text="cita.motivo" class="text-sm text-gray-600 dark:text-gray-400 mt-1"></p>
                                    <div class="mt-1">
                                        <span 
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                            :class="{
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/30 dark:text-yellow-200': cita.estado === 'Pendiente',
                                                'bg-blue-100 text-blue-800 dark:bg-blue-800/30 dark:text-blue-200': cita.estado === 'Confirmado',
                                                'bg-green-100 text-green-800 dark:bg-green-800/30 dark:text-green-200': cita.estado === 'Realizada',
                                                'bg-red-100 text-red-800 dark:bg-red-800/30 dark:text-red-200': cita.estado === 'Cancelado',
                                                'bg-gray-100 text-gray-800 dark:bg-gray-800/30 dark:text-gray-200': cita.estado !== 'Pendiente' && cita.estado !== 'Confirmado' && cita.estado !== 'Realizada' && cita.estado !== 'Cancelado'
                                            }"
                                            x-text="cita.estado"
                                        ></span>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <!-- CASO 1: Cita pendiente - Mostrar botón confirmar -->
                                    <template x-if="cita.estado === 'Pendiente'">
                                        <button 
                                            @click.prevent="$wire.confirmarCita(cita.id).then(result => { 
                                                if (result && result.estado) { 
                                                    cita.estado = result.estado;
                                                    $parent.actualizarEstadoCita(cita.id, result.estado);
                                                }
                                            })"
                                            class="px-3 py-1 text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 border border-blue-300 hover:border-blue-500 rounded"
                                        >
                                            Confirmar cita
                                        </button>
                                    </template>
                                    
                                    <!-- CASO 2: Cita confirmada - Mostrar botones de cancelar y crear consulta -->
                                    <template x-if="cita.estado === 'Confirmado'">
                                        <button 
                                            @click.prevent="
                                                // Mostrar indicador de carga
                                                $el.disabled = true;
                                                $el.innerHTML = 'Redirigiendo...';
                                                
                                                // Llamar al método y esperar respuesta
                                                $wire.crearConsulta(cita.id).catch(error => {
                                                    console.error('Error al crear consulta:', error);
                                                    $el.disabled = false;
                                                    $el.innerHTML = 'Crear consulta';
                                                });
                                            "
                                            class="px-3 py-1 text-sm font-medium text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 border border-green-300 hover:border-green-500 rounded"
                                        >
                                            Crear consulta
                                        </button>
                                    </template>
                                    
                                    <!-- Cancelar para estados Pendiente y Confirmado -->
                                    <template x-if="cita.estado === 'Pendiente' || cita.estado === 'Confirmado'">
                                        <button 
                                            @click.prevent="$wire.cancelarCita(cita.id).then(result => {
                                                if (result && result.estado) {
                                                    cita.estado = result.estado;
                                                    $parent.actualizarEstadoCita(cita.id, result.estado);
                                                }
                                            })"
                                            class="px-3 py-1 text-sm font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 border border-red-300 hover:border-red-500 rounded"
                                        >
                                            Cancelar cita
                                        </button>
                                    </template>

                                    <!-- Ver detalles para todos los estados -->
                                    <a 
                                        :href="'/admin/citas/citas/' + cita.id + (cita.estado === 'Cancelado' || cita.estado === 'Realizada' ? '/view' : '/edit')"
                                        class="px-3 py-1 text-sm font-medium text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300 border border-gray-300 hover:border-gray-500 rounded text-center"
                                    >
                                        <span x-text="cita.estado === 'Cancelado' || cita.estado === 'Realizada' ? 'Ver detalles' : 'Ver cita'"></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 flex justify-end">
                    <button 
                        @click="showModal = false" 
                        type="button" 
                        class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Guardar las citas por día en una variable global con ID único para este widget
        window.citasPorDia_{{ $this->getId() }} = {!! json_encode($citasPorDia) !!} || {};
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log("Widget de calendario inicializado con ID: {{ $this->getId() }}");
            inicializarCalendario_{{ $this->getId() }}();
        });
        
        function inicializarCalendario_{{ $this->getId() }}() {
            // Obtener el contenedor del calendario específico de este widget
            const calendarioContainer = document.getElementById('calendario-widget-{{ $this->getId() }}');
            if (!calendarioContainer) {
                console.error("No se encontró el contenedor del calendario con ID: calendario-widget-{{ $this->getId() }}");
                return;
            }
            
            // 1. Configurar los eventos para los días del calendario
            const diasDelMes = calendarioContainer.querySelectorAll('.dia-calendario');
            console.log(`Se encontraron ${diasDelMes.length} días del mes para configurar eventos`);
            
            diasDelMes.forEach(function(dia) {
                dia.addEventListener('click', function() {
                    const diaNumero = dia.getAttribute('data-dia');
                    if (!diaNumero) return;
                    
                    const citasDelDia = window.citasPorDia_{{ $this->getId() }}[diaNumero] || [];
                    console.log(`Mostrando modal para día ${diaNumero} con ${citasDelDia.length} citas`);
                    
                    // Disparar evento para mostrar el modal
                    window.dispatchEvent(new CustomEvent('mostrar-citas-dia', {
                        detail: {
                            dia: diaNumero + ' de ' + '{{ $mesActual }} {{ $anio }}',
                            citas: citasDelDia
                        }
                    }));
                });
            });
            
            // 2. Configurar los eventos para las vistas previas de citas
            const citasPreview = calendarioContainer.querySelectorAll('.cita-preview');
            console.log(`Se encontraron ${citasPreview.length} citas preview para configurar eventos`);
            
            citasPreview.forEach(function(citaPreview) {
                citaPreview.addEventListener('click', function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    const citaId = this.getAttribute('data-cita-id');
                    if (!citaId) return;
                    
                    // Encontrar el día de esta cita
                    let diaCita = null;
                    let citaData = null;
                    
                    // Buscar en todas las citas del mes
                    Object.entries(window.citasPorDia_{{ $this->getId() }}).forEach(([dia, citas]) => {
                        const citaEncontrada = citas.find(c => c.id == citaId);
                        if (citaEncontrada) {
                            diaCita = dia;
                            citaData = citaEncontrada;
                        }
                    });
                    
                    if (diaCita && citaData) {
                        // Mostrar el modal solo con esta cita específica
                        console.log(`Mostrando modal para cita ${citaId} del día ${diaCita}`);
                        
                        window.dispatchEvent(new CustomEvent('mostrar-citas-dia', {
                            detail: {
                                dia: diaCita + ' de ' + '{{ $mesActual }} {{ $anio }}',
                                citas: [citaData]
                            }
                        }));
                    }
                });
            });
        }
        
        // Re-inicializar cuando Livewire actualiza el DOM
        document.addEventListener('livewire:update', function() {
            setTimeout(function() {
                console.log("Re-inicializando calendario después de actualización Livewire");
                inicializarCalendario_{{ $this->getId() }}();
            }, 100);
        });
        
        // Escuchar eventos de Livewire
        document.addEventListener('livewire:initialized', () => {
            // Añadir manejador para redirección a consulta
            Livewire.on('redirigirConsulta', (data) => {
                console.log('Redirigiendo a:', data.url);
                // Redirigir a la URL de creación de consulta
                window.location.href = data.url;
            });
            
            // Actualizar el widget cuando se modifica una cita
            Livewire.on('citasActualizadas', () => {
                console.log('Citas actualizadas, refrescando widget');
            });
        });
    </script>

    <style>
        /* Estilos para el modal */
        [x-cloak] { display: none !important; }
        
        /* Hacer que los días del calendario sean más interactivos */
        .dia-calendario {
            transition: all 0.2s ease;
        }
        .dia-calendario:hover {
            box-shadow: inset 0 0 0 2px rgba(59, 130, 246, 0.5);
        }
        
        /* Hacer que las citas sean más interactivas */
        .cita-preview {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .cita-preview:hover {
            opacity: 0.8;
        }
    </style>
</x-filament-widgets::widget>
