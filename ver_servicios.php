<?php

require_once 'vendor/autoload.php';

use App\Models\Servicio;

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Servicios disponibles:\n";
foreach (Servicio::all() as $servicio) {
    echo "ID: {$servicio->id} - {$servicio->nombre} - L.{$servicio->precio_unitario}\n";
}
