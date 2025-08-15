<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Servicio;

echo "=== ACTUALIZANDO SERVICIOS CON IMPUESTO ===\n";

// Actualizar servicios sin impuesto para asignarles ISV (ID: 1)
$serviciosSinImpuesto = [2, 3, 4, 5, 6]; // IDs de servicios sin impuesto
foreach ($serviciosSinImpuesto as $servicioId) {
    $servicio = Servicio::find($servicioId);
    if ($servicio) {
        $servicio->impuesto_id = 1; // ISV 15%
        $servicio->save();
        echo "Servicio {$servicio->nombre} actualizado con ISV 15%\n";
    }
}
echo "Servicios actualizados correctamente\n";
