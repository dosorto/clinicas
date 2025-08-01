<x-filament-panels::page>
    <x-filament-panels::form wire:submit="usarCalculo">
        {{ $this->form }}
        
        <div class="mt-6">
            <h3 class="text-lg font-medium text-gray-700 mb-3">Detalle de Servicios Realizados</h3>
            
            <div class="rounded-lg overflow-hidden border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Servicio</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $porcentaje }}%</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Aquí se mostrarían los servicios desde la base de datos -->
                        <!-- Esta es una versión estática de ejemplo -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ date('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Juan Pérez</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Consulta general</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">L. 500.00</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">L. {{ number_format(500 * ($porcentaje/100), 2) }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ date('d/m/Y', strtotime('-2 days')) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">María López</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Procedimiento especializado</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">L. 1,800.00</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">L. {{ number_format(1800 * ($porcentaje/100), 2) }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ date('d/m/Y', strtotime('-5 days')) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Carlos Rodríguez</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Evaluación de seguimiento</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">L. 350.00</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">L. {{ number_format(350 * ($porcentaje/100), 2) }}</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">Total:</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">L. 2,650.00</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">L. {{ number_format(2650 * ($porcentaje/100), 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-6 flex justify-between">
                <x-filament::button
                    color="gray"
                    tag="a"
                    :href="route('filament.admin.resources.contabilidad-medica.cargo-medicos.create')"
                >
                    Cancelar
                </x-filament::button>
                
                <x-filament::button
                    type="submit"
                    color="success"
                >
                    Usar este cálculo
                </x-filament::button>
            </div>
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>
