<x-filament-panels::page>
    {{-- Panel de información de la factura --}}
    <x-filament::section>
        <x-slot name="heading">
            Información de la Factura
        </x-slot>
        
        @php
            // Información de contexto usando el record disponible
            $pacienteNombre = 'Paciente no encontrado';
            $medicoNombre = 'Médico no encontrado';
            $centroNombre = auth()->user()->centro?->nombre_centro ?? 'Centro Médico';
            $fecha = now()->format('d/m/Y');
            
            if ($this->record) {
                $consulta = $this->record;
                
                if ($consulta->paciente && $consulta->paciente->persona) {
                    $pacienteNombre = $consulta->paciente->persona->nombre_completo;
                }
                
                if ($consulta->medico && $consulta->medico->persona) {
                    $medicoNombre = $consulta->medico->persona->nombre_completo;
                }
                
                if ($consulta->centro) {
                    $centroNombre = $consulta->centro->nombre_centro;
                }
                
                $fecha = $consulta->created_at->format('d/m/Y');
            }
            
            // Obtener información del CAI disponible
            $centroId = auth()->user()->centro_id;
            $cai = \App\Services\CaiNumerador::obtenerCAIDisponible($centroId);
            
            // Verificar estado guardado del CAI en PHP
            $consultaId = $this->record->id;
            $caiEstadoGuardado = false; // Por defecto false
            
            // Intentar leer desde el request o parámetros si viene de otra página
            if (request()->has('usa_cai')) {
                $caiEstadoGuardado = request()->get('usa_cai') == '1';
            }
        @endphp
        
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-6 border border-blue-200 dark:border-blue-700">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Paciente -->
                <div class="text-center">
                    <div class="flex justify-center mb-3">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-800 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Paciente</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $pacienteNombre }}</p>
                </div>
                
                <!-- Médico -->
                <div class="text-center">
                    <div class="flex justify-center mb-3">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Médico</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $medicoNombre }}</p>
                </div>
                
                <!-- Centro -->
                <div class="text-center">
                    <div class="flex justify-center mb-3">
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-800 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Centro</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $centroNombre }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $fecha }}</p>
                </div>
            </div>
            
            <!-- Información de la Factura -->
            <div class="mt-6 pt-6 border-t border-blue-200 dark:border-blue-700">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Número de Factura -->
                    <div class="text-center">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Número de Factura</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Se generará automáticamente</p>
                    </div>
                    
                    <!-- ¿Emitir con CAI? -->
                    <div class="text-center">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">¿Generar Factura?</p>
                        <div class="flex items-center justify-center space-x-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" 
                                       id="emitir_cai_toggle"
                                       {{ $caiEstadoGuardado ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:focus:ring-blue-400">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active para una Factura</span>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Desactive para generar una factura provisional</p>
                    </div>
                    
                    <!-- Estado -->
                    <div class="text-center">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Estado Previsto</p>
                        <div id="estado_factura_preview" class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-800 dark:bg-orange-800/30 dark:text-orange-200">
                            <span id="estado_factura_text">Pre-Factura</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Basado en pagos configurados</p>
                    </div>
                </div>
                
                <!-- Información del CAI -->
                <div id="cai-info-section" style="display: {{ $caiEstadoGuardado ? 'block' : 'none' }};" class="mt-4 pt-4 border-t border-blue-200 dark:border-blue-700">
                    @if($cai)
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-700">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-medium text-green-800 dark:text-green-300 mb-1">CAI Disponible</p>
                                <p class="text-sm font-mono font-medium text-green-800 dark:text-green-200">{{ $cai->cai_codigo }}</p>
                                <p class="text-xs text-green-600 dark:text-green-400">Se emitirá automáticamente con CAI al facturar</p>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-4 border border-amber-200 dark:border-amber-700">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">Sin CAI disponible</p>
                                <p class="text-xs text-amber-600 dark:text-amber-400">Se emitirá como proforma sin número fiscal</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </x-filament::section>

    {{-- Panel de resumen --}}
    @php($subtotal = $this->getServiciosSubtotal())
    @php($impuestos = $this->getServiciosImpuesto())
    @php($total = $this->getServiciosTotal())
    @php($cantidad = $this->getCantidadServicios())

    @if ($subtotal > 0)
        <x-filament::section>
            <x-slot name="heading">
                Resumen de Servicios
            </x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                <div class="text-center md:text-left">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Servicios agregados
                    </p>
                    <p class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                        {{ $cantidad }} servicio(s)
                    </p>
                </div>

                <div class="text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Descuento a Aplicar</p>
                    <select id="descuento_select" 
                            name="descuento_aplicado"
                            class="w-full max-w-xs rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm">
                        <option value="">Sin descuento</option>
                        @foreach(\App\Models\Descuento::where('centro_id', Auth::user()->centro_id)->get() as $descuento)
                            <option value="{{ $descuento->id }}" 
                                    data-tipo="{{ $descuento->tipo }}"
                                    data-valor="{{ $descuento->valor }}"
                                    data-porcentaje="{{ $descuento->tipo == 'PORCENTAJE' ? $descuento->valor : 0 }}">
                                {{ $descuento->nombre }} ({{ $descuento->valor }}{{ $descuento->tipo == 'PORCENTAJE' ? '%' : '' }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Selecciona un descuento para la factura</p>
                </div>

                <div class="text-center md:text-right">
                   {{-- Total Final --}}
                    <p class="text-xs font-medium text-green-600 dark:text-green-400 uppercase tracking-wide mb-1">Total Final</p>
                    <p class="text-xl font-bold text-green-800 dark:text-green-200" id="total_final_display">
                        L. {{ number_format($total, 2) }}
                    </p>
                    <p class="text-xs text-green-600 dark:text-green-400">Subtotal + Impuestos - Descuentos</p>
                </div>
            </div>
            
            {{-- Sección de Totales Detallados --}}
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Desglose de Totales</h4>
                
                <div class="flex flex-row gap-4 text-center">
                    {{-- Subtotal --}}
                    <div class="flex-1 bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-700">
                        <p class="text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wide mb-1">Subtotal</p>
                        <p class="text-lg font-bold text-blue-800 dark:text-blue-200 subtotal-amount" id="subtotal_display">
                            L. {{ number_format($subtotal, 2) }}
                        </p>
                        <p class="text-xs text-blue-600 dark:text-blue-400">Sin impuestos ni descuentos</p>
                    </div>
                    
                    {{-- Impuestos --}}
                    <div class="text-center flex-1 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 border border-yellow-200 dark:border-yellow-700">
                        <p class="text-xs font-medium text-yellow-600 dark:text-yellow-400 uppercase tracking-wide mb-1">Impuestos</p>
                        <p class="text-lg font-bold text-yellow-800 dark:text-yellow-200 total-impuestos" id="impuestos_display">
                            L. {{ number_format($impuestos, 2) }}
                        </p>
                        <p class="text-xs text-yellow-600 dark:text-yellow-400">Total de impuestos</p>
                    </div>
                    
                    {{-- Descuentos --}}
                    <div class="text-center flex-1 bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 border border-purple-200 dark:border-purple-700">
                        <p class="text-xs font-medium text-purple-600 dark:text-purple-400 uppercase tracking-wide mb-1">Descuentos</p>
                        <p class="text-lg font-bold text-purple-800 dark:text-purple-200 total-descuentos" id="descuentos_display">
                            L. 0.00
                        </p>
                        <p class="text-xs text-purple-600 dark:text-purple-400">Descuento aplicado</p>
                    </div>
                </div>
            </div>
        </x-filament::section>
    @else
        <x-filament::section>
            <div class="text-center py-8">
                <div class="text-gray-400 text-6xl mb-4">📋</div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No hay servicios agregados</h3>
                <p class="text-gray-600 dark:text-gray-400">Usa el botón <strong>"Agregar Servicios"</strong> para comenzar.</p>
            </div>
        </x-filament::section>
    @endif

    {{-- Tabla de servicios --}}
    <div class="mt-6">
        {{ $this->table }}
    </div>

    <script>
        // Script más simple y robusto - SOLO gestiona el estado, no interfiere con Livewire
        window.ClinicaCAISystem = (function() {
            'use strict';
            
            const CONSULTA_ID = '{{ $this->record->id }}';
            const CAI_KEY = `cai_toggle_state_${CONSULTA_ID}`;
            const DESCUENTO_KEY = `selected_descuento_${CONSULTA_ID}`;
            
            const totalesBase = {
                subtotal: {{ $subtotal }},
                impuestos: {{ $impuestos }},
                total: {{ $total }}
            };

            let isInitialized = false;

            // Función simple para manejar CAI
            function handleCAIToggle() {
                const toggle = document.getElementById('emitir_cai_toggle');
                const section = document.getElementById('cai-info-section');
                
                if (!toggle || !section) return;

                // Solo guardar cuando el usuario cambie manualmente
                if (isInitialized) {
                    const isChecked = toggle.checked;
                    section.style.display = isChecked ? 'block' : 'none';
                    
                    // Guardar en múltiples lugares
                    sessionStorage.setItem(CAI_KEY, isChecked);
                    localStorage.setItem(CAI_KEY, isChecked);
                    
                    console.log('✅ CAI estado guardado:', isChecked);
                }
            }

            // Función para calcular descuentos
            function calcularDescuento() {
                const select = document.getElementById('descuento_select');
                if (!select) return;

                const selectedOption = select.options[select.selectedIndex];
                let descuento = 0;

                if (selectedOption && selectedOption.value) {
                    const tipo = selectedOption.getAttribute('data-tipo');
                    const valor = parseFloat(selectedOption.getAttribute('data-valor') || 0);

                    if (tipo === 'PORCENTAJE' && valor > 0) {
                        descuento = totalesBase.subtotal * (valor / 100);
                    } else if (tipo === 'MONTO' && valor > 0) {
                        descuento = valor;
                    }
                }

                // Actualizar displays
                const totalFinal = totalesBase.subtotal + totalesBase.impuestos - descuento;
                
                const descuentosDisplay = document.getElementById('descuentos_display');
                const totalDisplay = document.getElementById('total_final_display');
                
                if (descuentosDisplay) descuentosDisplay.textContent = 'L. ' + descuento.toFixed(2);
                if (totalDisplay) totalDisplay.textContent = 'L. ' + totalFinal.toFixed(2);

                // Guardar descuento
                if (selectedOption && selectedOption.value) {
                    const data = {
                        id: selectedOption.value,
                        tipo: selectedOption.getAttribute('data-tipo'),
                        valor: selectedOption.getAttribute('data-valor'),
                        nombre: selectedOption.text,
                        monto_calculado: descuento
                    };
                    sessionStorage.setItem(DESCUENTO_KEY, JSON.stringify(data));
                } else {
                    sessionStorage.removeItem(DESCUENTO_KEY);
                }
            }

            // Función para restaurar estados
            function restaurarEstados() {
                // Restaurar CAI
                const toggle = document.getElementById('emitir_cai_toggle');
                const section = document.getElementById('cai-info-section');
                
                if (toggle && section) {
                    // Leer estado guardado
                    let estadoGuardado = sessionStorage.getItem(CAI_KEY);
                    if (estadoGuardado === null) {
                        estadoGuardado = localStorage.getItem(CAI_KEY);
                    }

                    if (estadoGuardado !== null) {
                        const isChecked = estadoGuardado === 'true';
                        toggle.checked = isChecked;
                        section.style.display = isChecked ? 'block' : 'none';
                        console.log('🔄 CAI restaurado:', isChecked);
                    }
                }

                // Restaurar descuento
                const descuentoSelect = document.getElementById('descuento_select');
                if (descuentoSelect) {
                    const savedDescuento = sessionStorage.getItem(DESCUENTO_KEY);
                    if (savedDescuento) {
                        try {
                            const data = JSON.parse(savedDescuento);
                            descuentoSelect.value = data.id;
                        } catch (e) {
                            console.log('Error restaurando descuento:', e);
                        }
                    }
                }

                // Calcular descuentos
                calcularDescuento();
            }

            // Función de inicialización
            function init() {
                console.log('🚀 Inicializando ClinicaCAISystem...');

                // Configurar CAI toggle
                const toggle = document.getElementById('emitir_cai_toggle');
                if (toggle) {
                    // Remover listener anterior si existe
                    toggle.removeEventListener('change', handleCAIToggle);
                    // Agregar nuevo listener
                    toggle.addEventListener('change', handleCAIToggle);
                }

                // Configurar descuentos
                const descuentoSelect = document.getElementById('descuento_select');
                if (descuentoSelect) {
                    descuentoSelect.removeEventListener('change', calcularDescuento);
                    descuentoSelect.addEventListener('change', calcularDescuento);
                }

                // Restaurar estados después de configurar listeners
                setTimeout(() => {
                    restaurarEstados();
                    isInitialized = true;
                    console.log('✅ Sistema inicializado');
                }, 100);
            }

            // Función para verificar y corregir estado CAI
            function verificarEstadoCAI() {
                const toggle = document.getElementById('emitir_cai_toggle');
                const section = document.getElementById('cai-info-section');
                
                if (toggle && section) {
                    const estadoGuardado = sessionStorage.getItem(CAI_KEY) || localStorage.getItem(CAI_KEY);
                    if (estadoGuardado !== null) {
                        const shouldBeChecked = estadoGuardado === 'true';
                        const shouldBeVisible = shouldBeChecked;
                        
                        if (toggle.checked !== shouldBeChecked) {
                            toggle.checked = shouldBeChecked;
                            console.log('🔧 Checkbox CAI corregido');
                        }
                        
                        const isVisible = section.style.display !== 'none';
                        if (isVisible !== shouldBeVisible) {
                            section.style.display = shouldBeVisible ? 'block' : 'none';
                            console.log('🔧 Sección CAI corregida');
                        }
                    }
                }
            }

            // API pública
            return {
                init: init,
                calcularDescuento: calcularDescuento,
                verificarEstadoCAI: verificarEstadoCAI,
                handleCAIToggle: handleCAIToggle
            };

        // Múltiples puntos de inicialización
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                ClinicaCAISystem.init();
            });
        } else {
            ClinicaCAISystem.init();
        }

        // Eventos de Livewire
        document.addEventListener('livewire:load', () => {
            setTimeout(() => ClinicaCAISystem.init(), 200);
        });

        document.addEventListener('livewire:update', () => {
            setTimeout(() => {
                ClinicaCAISystem.init();
                // Verificar estado después de actualización
                setTimeout(() => ClinicaCAISystem.verificarEstadoCAI(), 100);
            }, 100);
        });

        // Evento personalizado para refresh
        document.addEventListener('refresh-totales', function() {
            console.log('🔄 Evento refresh-totales recibido - NO recargando página');
            
            // En lugar de recargar, solo actualizar totales base y recalcular
            setTimeout(() => {
                ClinicaCAISystem.init();
                ClinicaCAISystem.calcularDescuento();
            }, 100);
        });

        // Verificación periódica (más frecuente)
        setInterval(() => {
            ClinicaCAISystem.verificarEstadoCAI();
        }, 1000);

        // Observer para cambios DOM
        const observer = new MutationObserver(function(mutations) {
            let shouldReinit = false;
            
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    for (let node of mutation.addedNodes) {
                        if (node.nodeType === 1 && (
                            node.id === 'emitir_cai_toggle' ||
                            node.id === 'cai-info-section' ||
                            (node.querySelector && (
                                node.querySelector('#emitir_cai_toggle') ||
                                node.querySelector('#cai-info-section')
                            ))
                        )) {
                            shouldReinit = true;
                            break;
                        }
                    }
                }
            });

            if (shouldReinit) {
                console.log('🔄 DOM cambió, reinicializando...');
                setTimeout(() => {
                    ClinicaCAISystem.init();
                    setTimeout(() => ClinicaCAISystem.verificarEstadoCAI(), 100);
                }, 50);
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        console.log('✅ Sistema CAI cargado');
    </script>

    {{-- Script adicional para forzar estado inicial --}}
    <script>
        // Ejecutar inmediatamente para establecer estado inicial
        (function() {
            const consultaId = '{{ $this->record->id }}';
            const caiKey = `cai_toggle_state_${consultaId}`;
            
            // Si hay estado guardado, aplicarlo inmediatamente
            const estadoGuardado = sessionStorage.getItem(caiKey) || localStorage.getItem(caiKey);
            if (estadoGuardado === 'true') {
                // Usar un pequeño delay para asegurar que el DOM esté listo
                setTimeout(() => {
                    const section = document.getElementById('cai-info-section');
                    const toggle = document.getElementById('emitir_cai_toggle');
                    
                    if (section) {
                        section.style.display = 'block';
                        console.log('🚀 CAI forzado a mostrar');
                    }
                    if (toggle) {
                        toggle.checked = true;
                    }
                }, 10);
            }
        })();
    </script>
</x-filament-panels::page>