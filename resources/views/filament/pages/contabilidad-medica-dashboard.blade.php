<x-filament-panels::page>
    <!-- Widget de estadísticas en la parte superior -->
    <div class="space-y-6">
        {{ $this->statsOverview }}
        
        <!-- Tarjetas de acciones rápidas -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <!-- Tarjeta: Crear Cargo Médico -->
            <div class="relative p-6 bg-white rounded-lg shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
                <div class="flex items-center gap-x-3">
                    <div class="p-2 bg-success-100 rounded-lg dark:bg-success-500/20">
                        <x-heroicon-o-plus-circle class="w-6 h-6 text-success-500 dark:text-success-400" />
                    </div>
                    <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">Registrar Cargo Médico</h3>
                </div>
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                    Registre un nuevo cargo de honorarios médicos para su posterior pago y liquidación.
                </p>
                <div class="mt-5">
                    <a href="{{ route('filament.admin.resources.contabilidad-medica.cargo-medicos.create') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-success-600 border border-transparent rounded-lg shadow-sm hover:bg-success-700 dark:bg-success-500 dark:hover:bg-success-600 focus:outline-none focus:ring-2 focus:ring-success-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        Crear Cargo
                    </a>
                </div>
            </div>
            
            <!-- Tarjeta: Registrar Pago -->
            <div class="relative p-6 bg-white rounded-lg shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
                <div class="flex items-center gap-x-3">
                    <div class="p-2 bg-primary-100 rounded-lg dark:bg-primary-500/20">
                        <x-heroicon-o-banknotes class="w-6 h-6 text-primary-500 dark:text-primary-400" />
                    </div>
                    <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">Registrar Pago</h3>
                </div>
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                    Registre un pago para liquidaciones pendientes y genere recibos automáticamente.
                </p>
                <div class="mt-5">
                    <a href="{{ route('filament.admin.resources.contabilidad-medica.pago-honorarios.create') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-lg shadow-sm hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        Registrar Pago
                    </a>
                </div>
            </div>
            
            <!-- Tarjeta: Ver Reportes -->
            <div class="relative p-6 bg-white rounded-lg shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
                <div class="flex items-center gap-x-3">
                    <div class="p-2 bg-warning-100 rounded-lg dark:bg-warning-500/20">
                        <x-heroicon-o-document-chart-bar class="w-6 h-6 text-warning-500 dark:text-warning-400" />
                    </div>
                    <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">Reportes Contables</h3>
                </div>
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                    Genere reportes de pagos, cargos pendientes y estado de cuenta de médicos.
                </p>
                <div class="mt-5">
                    <button 
                        x-on:click="$dispatch('open-modal', { id: 'generate-report-modal' })" 
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-warning-600 border border-transparent rounded-lg shadow-sm hover:bg-warning-700 dark:bg-warning-500 dark:hover:bg-warning-600 focus:outline-none focus:ring-2 focus:ring-warning-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                    >
                        Ver Reportes
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Tabla de cargos pendientes -->
        <div class="p-2 bg-white rounded-xl shadow dark:bg-gray-800">
            {{ $this->table }}
        </div>
    </div>
    
    <!-- Modal para generar reportes -->
    <x-filament::modal id="generate-report-modal" width="md" :slide-over="false" display-classes="block">
        <x-slot name="heading">Generar Reporte Contable</x-slot>
        
        <x-slot name="description">Seleccione el tipo de reporte y el período</x-slot>
        
        <div class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="report-type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Reporte</label>
                    <select id="report-type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600">
                        <option value="pagos">Pagos Realizados</option>
                        <option value="pendientes">Cargos Pendientes</option>
                        <option value="medicos">Estado de Cuenta Médicos</option>
                        <option value="centro">Resumen del Centro</option>
                    </select>
                </div>
                
                <div>
                    <label for="report-period" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Período</label>
                    <select id="report-period" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600">
                        <option value="month">Mes Actual</option>
                        <option value="quarter">Trimestre Actual</option>
                        <option value="year">Año Actual</option>
                        <option value="custom">Personalizado</option>
                    </select>
                </div>
            </div>
            
            <div class="hidden" id="custom-date-range">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="date-from" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Desde</label>
                        <input type="date" id="date-from" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    
                    <div>
                        <label for="date-to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hasta</label>
                        <input type="date" id="date-to" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600">
                    </div>
                </div>
            </div>
            
            <div>
                <label for="format" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Formato</label>
                <div class="mt-1 flex space-x-4">
                    <div class="flex items-center">
                        <input id="format-pdf" name="format" type="radio" checked class="h-4 w-4 text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600">
                        <label for="format-pdf" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">PDF</label>
                    </div>
                    <div class="flex items-center">
                        <input id="format-excel" name="format" type="radio" class="h-4 w-4 text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600">
                        <label for="format-excel" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">Excel</label>
                    </div>
                </div>
            </div>
        </div>
        
        <x-slot name="footerActions">
            <x-filament::button
                color="gray"
                x-on:click="$dispatch('close-modal', { id: 'generate-report-modal' })"
            >
                Cancelar
            </x-filament::button>
            
            <x-filament::button
                type="submit"
            >
                Generar Reporte
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>
