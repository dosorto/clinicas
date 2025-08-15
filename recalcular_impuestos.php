<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\FacturaDetalle;
use App\Models\Servicio;

echo "=== RECALCULANDO IMPUESTOS EN FACTURAS DETALLE ===\n";

// Obtener todos los detalles sin factura (pendientes)
$detalles = FacturaDetalle::whereNull('factura_id')
    ->with(['servicio.impuesto'])
    ->get();

echo "Facturas detalle a recalcular: " . $detalles->count() . "\n\n";

foreach ($detalles as $detalle) {
    echo "Procesando detalle ID: {$detalle->id} - Servicio: {$detalle->servicio->nombre}\n";
    
    $servicio = $detalle->servicio;
    $cantidad = $detalle->cantidad;
    $subtotal = $servicio->precio_unitario * $cantidad;
    
    // Calcular impuesto
    $impuesto_monto = 0;
    $impuesto_id = null;
    
    if ($servicio->es_exonerado !== 'SI' && $servicio->impuesto) {
        $impuesto_monto = ($subtotal * $servicio->impuesto->porcentaje) / 100;
        $impuesto_id = $servicio->impuesto->id;
        echo "  Calculando impuesto: {$servicio->impuesto->porcentaje}% de L.{$subtotal} = L.{$impuesto_monto}\n";
    } else {
        echo "  Sin impuesto (exonerado o sin impuesto configurado)\n";
    }
    
    $total_linea = $subtotal + $impuesto_monto;
    
    // Actualizar el detalle
    $detalle->update([
        'subtotal' => $subtotal,
        'impuesto_id' => $impuesto_id,
        'impuesto_monto' => $impuesto_monto,
        'total_linea' => $total_linea
    ]);
    
    echo "  Actualizado: Subtotal=L.{$subtotal}, Impuesto=L.{$impuesto_monto}, Total=L.{$total_linea}\n\n";
}

echo "Recálculo completado!\n";
