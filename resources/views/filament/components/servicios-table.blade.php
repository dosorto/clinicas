<table class="min-w-full text-sm text-left">
    <thead class="bg-gray-50 dark:bg-gray-800">
        <tr>
            <th class="px-4 py-2">Servicio</th>
            <th class="px-4 py-2 text-center">Cant.</th>
            <th class="px-4 py-2 text-right">Total (L.)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($detalles as $d)
            <tr class="border-b dark:border-gray-700">
                <td class="px-4 py-1">{{ $d->servicio->nombre }}</td>
                <td class="px-4 py-1 text-center">{{ $d->cantidad }}</td>
                <td class="px-4 py-1 text-right">{{ number_format($d->total_linea, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
