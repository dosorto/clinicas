<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Request::capture();
$response = $kernel->handle($request);

// Test de creación de factura
echo "=== TEST DE CREACIÓN DE FACTURA ===\n";

try {
    // Buscar una consulta existente con servicios pendientes
    $consulta = \App\Models\Consulta::with(['paciente.persona', 'medico.persona'])
        ->first();
    
    if (!$consulta) {
        echo "❌ No hay consultas disponibles\n";
        exit;
    }
    
    // Verificar si hay detalles pendientes para esta consulta
    $detalles = \App\Models\FacturaDetalle::where('consulta_id', $consulta->id)
        ->whereNull('factura_id')
        ->with('servicio.impuesto')
        ->get();
    
    if ($detalles->isEmpty()) {
        echo "❌ No hay servicios pendientes para esta consulta\n";
        exit;
    }
    
    echo "✅ Consulta encontrada: ID {$consulta->id}\n";
    echo "   Paciente: {$consulta->paciente->persona->primer_nombre} {$consulta->paciente->persona->primer_apellido}\n";
    echo "   Médico: {$consulta->medico->persona->primer_nombre} {$consulta->medico->persona->primer_apellido}\n";
    
    // Obtener detalles pendientes
    $detalles = \App\Models\FacturaDetalle::where('consulta_id', $consulta->id)
        ->whereNull('factura_id')
        ->with('servicio.impuesto')
        ->get();
    
    $subtotal = $detalles->sum('subtotal');
    $impuestos = $detalles->sum('impuesto_monto');
    
    echo "   Servicios pendientes: {$detalles->count()}\n";
    echo "   Subtotal: L. " . number_format($subtotal, 2) . "\n";
    echo "   Impuestos: L. " . number_format($impuestos, 2) . "\n";
    echo "   Total: L. " . number_format($subtotal + $impuestos, 2) . "\n";
    
    // Datos para crear la factura
    $facturaData = [
        'consulta_id' => $consulta->id,
        'paciente_id' => $consulta->paciente_id,
        'medico_id' => $consulta->medico_id,
        'fecha_emision' => now()->format('Y-m-d'),
        'estado' => 'PENDIENTE',
        'subtotal' => round($subtotal, 2),
        'impuesto_total' => round($impuestos, 2),
        'descuento_total' => 0,
        'total' => round($subtotal + $impuestos, 2),
        'usa_cai' => false,
        'created_by' => 1, // Usuario admin
        'centro_id' => $consulta->centro_id ?? 1,
    ];
    
    echo "\n=== CREANDO FACTURA ===\n";
    
    $factura = \App\Models\Factura::create($facturaData);
    
    echo "✅ Factura creada: ID {$factura->id}\n";
    
    // Asignar detalles a la factura
    foreach ($detalles as $detalle) {
        $detalle->update(['factura_id' => $factura->id]);
    }
    
    echo "✅ {$detalles->count()} detalles asignados a la factura\n";
    
    // Actualizar estado de pago
    $factura->refresh();
    $factura->actualizarEstadoPago();
    
    echo "✅ Estado actualizado: {$factura->estado}\n";
    echo "✅ TEST COMPLETADO EXITOSAMENTE\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

$kernel->terminate($request, $response);
