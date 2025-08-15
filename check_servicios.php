<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Servicio;
use App\Models\Impuesto;

echo "=== TODOS LOS SERVICIOS ===\n";
$servicios = Servicio::with('impuesto')->get();
foreach ($servicios as $servicio) {
    echo "ID: {$servicio->id} - {$servicio->nombre}\n";
    echo "  Precio: L. {$servicio->precio_unitario}\n";
    echo "  Es exonerado: {$servicio->es_exonerado}\n";
    echo "  Impuesto ID: " . ($servicio->impuesto_id ?? 'NULL') . "\n";
    if ($servicio->impuesto) {
        echo "  Impuesto: {$servicio->impuesto->nombre} ({$servicio->impuesto->porcentaje}%)\n";
    } else {
        echo "  No tiene impuesto\n";
    }
    echo "\n";
}

echo "=== IMPUESTOS DISPONIBLES ===\n";
$impuestos = Impuesto::all();
foreach ($impuestos as $impuesto) {
    echo "ID: {$impuesto->id} - {$impuesto->nombre} ({$impuesto->porcentaje}%)\n";
}
