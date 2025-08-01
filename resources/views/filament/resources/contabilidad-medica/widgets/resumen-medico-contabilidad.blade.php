<div class="p-4 bg-white rounded-xl shadow dark:bg-gray-800">
    @if($record)
    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
        Resumen Contable: {{ $record->persona->nombre_completo ?? 'Médico' }}
    </h3>
    
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <!-- Tarjeta: Honorarios Pendientes -->
        <div class="bg-danger-50 rounded-lg p-4 border border-danger-200 dark:bg-danger-900/20 dark:border-danger-700">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-sm text-danger-600 dark:text-danger-400">Honorarios Pendientes</p>
                    <h4 class="text-2xl font-bold text-danger-700 dark:text-danger-300">
                        L. {{ number_format($this->getResumenContable()['pendiente'] ?? 0, 2) }}
                    </h4>
                </div>
                <div class="p-2 bg-danger-100 rounded-full dark:bg-danger-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-danger-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
            <div class="text-xs text-danger-600 dark:text-danger-400">
                <a href="{{ route('filament.admin.resources.contabilidad-medica.cargo-medicos.index', ['tableFilters[medico][value]' => $record->id]) }}" class="flex items-center hover:underline">
                    Ver cargos pendientes
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>
        
        <!-- Tarjeta: Pagado este mes -->
        <div class="bg-primary-50 rounded-lg p-4 border border-primary-200 dark:bg-primary-900/20 dark:border-primary-700">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-sm text-primary-600 dark:text-primary-400">Pagado este mes</p>
                    <h4 class="text-2xl font-bold text-primary-700 dark:text-primary-300">
                        L. {{ number_format($this->getResumenContable()['pagado_mes'] ?? 0, 2) }}
                    </h4>
                </div>
                <div class="p-2 bg-primary-100 rounded-full dark:bg-primary-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
            <div class="text-xs text-primary-600 dark:text-primary-400">
                <a href="{{ route('filament.admin.resources.contabilidad-medica.pago-honorarios.index') }}" class="flex items-center hover:underline">
                    Ver pagos realizados
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>
        
        <!-- Tarjeta: Pagado este año -->
        <div class="bg-success-50 rounded-lg p-4 border border-success-200 dark:bg-success-900/20 dark:border-success-700">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-sm text-success-600 dark:text-success-400">Pagado este año</p>
                    <h4 class="text-2xl font-bold text-success-700 dark:text-success-300">
                        L. {{ number_format($this->getResumenContable()['pagado_ano'] ?? 0, 2) }}
                    </h4>
                </div>
                <div class="p-2 bg-success-100 rounded-full dark:bg-success-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-success-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h8V3a1 1 0 112 0v1h1a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2h1V3a1 1 0 011-1zm11 14a1 1 0 01-1 1H5a1 1 0 01-1-1V8h12v8zM9 9a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
            <div class="text-xs text-success-600 dark:text-success-400">
                <a href="#" class="flex items-center hover:underline">
                    Generar reporte anual
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 gap-6 mt-6 md:grid-cols-2">
        <!-- Gráfico de pagos por mes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Pagos por Mes</h4>
            
            <div class="h-48">
                <canvas id="pagosPorMes"></canvas>
            </div>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('pagosPorMes').getContext('2d');
                    
                    const chartData = @json($this->getPagosPorMes());
                    
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: chartData.labels,
                            datasets: [{
                                label: 'Pagos (L.)',
                                data: chartData.data,
                                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                                borderColor: 'rgba(59, 130, 246, 1)',
                                borderWidth: 2,
                                tension: 0.3,
                                pointBackgroundColor: 'white',
                                pointBorderColor: 'rgba(59, 130, 246, 1)',
                                pointBorderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return 'L. ' + value.toLocaleString();
                                        }
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
        </div>
        
        <!-- Contratos activos -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Contratos Activos</h4>
            
            @php
                $contratos = $this->getContratosActivos();
            @endphp
            
            @if(count($contratos) > 0)
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($contratos as $contrato)
                        <div class="py-3">
                            <div class="flex justify-between">
                                <div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $contrato->centro->nombre_centro ?? 'Centro no especificado' }}
                                    </span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($contrato->fecha_inicio)->format('d/m/Y') }} - 
                                        {{ \Carbon\Carbon::parse($contrato->fecha_fin)->format('d/m/Y') }}
                                    </p>
                                </div>
                                <div>
                                    @if($contrato->salario_mensual > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                            Mensual: L. {{ number_format($contrato->salario_mensual, 2) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-800 dark:text-amber-100">
                                            {{ $contrato->porcentaje_servicio }}% por servicio
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-3">
                    <a href="{{ route('filament.admin.resources.contabilidad-medica.contrato-medicos.index', ['tableFilters[medico][value]' => $record->id]) }}" class="text-xs text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 flex items-center">
                        Ver todos los contratos
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            @else
                <div class="py-4 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay contratos activos</p>
                    <a href="{{ route('filament.admin.resources.contabilidad-medica.contrato-medicos.create', ['medico_id' => $record->id]) }}" class="inline-flex items-center mt-2 text-xs text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">
                        Crear un contrato
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            @endif
        </div>
    </div>
    
    <div class="grid grid-cols-1 gap-6 mt-6 md:grid-cols-2">
        <!-- Cargos recientes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Cargos Recientes</h4>
            
            @php
                $cargos = $this->getCargosRecientes();
            @endphp
            
            @if(count($cargos) > 0)
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($cargos as $cargo)
                        <div class="py-3">
                            <div class="flex justify-between">
                                <div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $cargo->descripcion }}
                                    </span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($cargo->periodo_inicio)->format('d/m/Y') }} - 
                                        {{ \Carbon\Carbon::parse($cargo->periodo_fin)->format('d/m/Y') }}
                                    </p>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-sm font-medium mr-2">
                                        L. {{ number_format($cargo->total, 2) }}
                                    </span>
                                    
                                    @php
                                        $badgeColor = match($cargo->estado) {
                                            'pendiente' => 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100',
                                            'parcial' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100',
                                            'pagado' => 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100',
                                            'anulado' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100',
                                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100'
                                        };
                                    @endphp
                                    
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeColor }}">
                                        {{ ucfirst($cargo->estado) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-3">
                    <a href="{{ route('filament.admin.resources.contabilidad-medica.cargo-medicos.index', ['tableFilters[medico][value]' => $record->id]) }}" class="text-xs text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 flex items-center">
                        Ver todos los cargos
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            @else
                <div class="py-4 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay cargos recientes</p>
                    <a href="{{ route('filament.admin.resources.contabilidad-medica.cargo-medicos.create', ['medico_id' => $record->id]) }}" class="inline-flex items-center mt-2 text-xs text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">
                        Crear un cargo
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            @endif
        </div>
        
        <!-- Pagos recientes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Pagos Recientes</h4>
            
            @php
                $pagos = $this->getPagosRecientes();
            @endphp
            
            @if(count($pagos) > 0)
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($pagos as $pago)
                        <div class="py-3">
                            <div class="flex justify-between">
                                <div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        Pago #{{ $pago->id }}
                                    </span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}
                                        - {{ ucfirst($pago->metodo_pago) }}
                                    </p>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-sm font-medium mr-2 text-success-600 dark:text-success-400">
                                        L. {{ number_format($pago->monto, 2) }}
                                    </span>
                                    
                                    <a href="{{ route('filament.admin.resources.contabilidad-medica.pago-honorarios.generar-recibo', ['pagoId' => $pago->id]) }}" class="text-xs text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300" target="_blank">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-3">
                    <a href="{{ route('filament.admin.resources.contabilidad-medica.pago-honorarios.index') }}" class="text-xs text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 flex items-center">
                        Ver todos los pagos
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            @else
                <div class="py-4 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay pagos recientes</p>
                    <a href="{{ route('filament.admin.resources.contabilidad-medica.pago-honorarios.create') }}" class="inline-flex items-center mt-2 text-xs text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">
                        Registrar un pago
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            @endif
        </div>
    </div>
    
    <div class="mt-6 flex justify-center">
        <a href="{{ route('filament.admin.resources.contabilidad-medica.cargo-medicos.create', ['medico_id' => $record->id]) }}" class="inline-flex items-center justify-center mr-3 px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-success-600 hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Crear Cargo
        </a>
        
        <a href="{{ route('filament.admin.resources.contabilidad-medica.pago-honorarios.create', ['medico_id' => $record->id]) }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
            </svg>
            Registrar Pago
        </a>
    </div>
    @else
    <div class="text-center py-6">
        <p class="text-gray-500 dark:text-gray-400">Seleccione un médico para ver su resumen contable</p>
    </div>
    @endif
</div>
