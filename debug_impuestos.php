<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\FacturaDetalle;
use App\Models\Servicio;
use App\Models\Consulta;

echo "=== DEBUG DE IMPUESTOS ===\n\n";

// 1. Encontrar consultas con servicios
$consultasConServicios = FacturaDetalle::select('consulta_id')
    ->whereNull('factura_id')
    ->groupBy('consulta_id')
    ->get();

echo "Consultas con servicios: " . $consultasConServicios->count() . "\n\n";

foreach ($consultasConServicios as $item) {
    $consultaId = $item->consulta_id;
    echo "=== CONSULTA ID: $consultaId ===\n";
    
    // Verificar facturas detalle
    $detalles = FacturaDetalle::where('consulta_id', $consultaId)
        ->whereNull('factura_id')
        ->with(['servicio.impuesto'])
        ->get();

    echo "Cantidad de detalles: " . $detalles->count() . "\n";

    foreach ($detalles as $detalle) {
        echo "- Servicio: {$detalle->servicio->nombre}\n";
        echo "  Precio unitario: {$detalle->servicio->precio_unitario}\n";
        echo "  Cantidad: {$detalle->cantidad}\n";
        echo "  Subtotal: {$detalle->subtotal}\n";
        echo "  Impuesto ID: " . ($detalle->impuesto_id ?? 'NULL') . "\n";
        echo "  Impuesto monto: {$detalle->impuesto_monto}\n";
        echo "  Es exonerado: {$detalle->servicio->es_exonerado}\n";
        
        if ($detalle->servicio->impuesto) {
            echo "  Impuesto asociado: {$detalle->servicio->impuesto->nombre}\n";
            echo "  Porcentaje: {$detalle->servicio->impuesto->porcentaje}%\n";
        } else {
            echo "  No tiene impuesto asociado\n";
        }
        echo "  Total línea: {$detalle->total_linea}\n\n";
    }

    // Verificar los cálculos de totales
    $subtotal = FacturaDetalle::where('consulta_id', $consultaId)
        ->whereNull('factura_id')
        ->sum('subtotal');

    $impuestos = FacturaDetalle::where('consulta_id', $consultaId)
        ->whereNull('factura_id')
        ->sum('impuesto_monto');

    $total = FacturaDetalle::where('consulta_id', $consultaId)
        ->whereNull('factura_id')
        ->sum('total_linea');

    echo "TOTALES CONSULTA $consultaId:\n";
    echo "Subtotal: L. " . number_format($subtotal, 2) . "\n";
    echo "Impuestos: L. " . number_format($impuestos, 2) . "\n";
    echo "Total: L. " . number_format($total, 2) . "\n\n";
    echo "----------------------------------------\n\n";
}

// Verificar servicios disponibles con impuestos
echo "=== SERVICIOS CON IMPUESTOS ===\n";
$serviciosConImpuestos = Servicio::with('impuesto')
    ->whereHas('impuesto')
    ->get();

foreach ($serviciosConImpuestos as $servicio) {
    echo "Servicio: {$servicio->nombre}\n";
    echo "Precio: L. {$servicio->precio_unitario}\n";
    echo "Es exonerado: {$servicio->es_exonerado}\n";
    echo "Impuesto: {$servicio->impuesto->nombre} ({$servicio->impuesto->porcentaje}%)\n\n";
}
