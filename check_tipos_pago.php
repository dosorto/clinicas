<?php

require_once 'vendor/autoload.php';

use App\Models\TipoPago;

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Tipos de pago disponibles:\n";
foreach (TipoPago::all() as $tipo) {
    echo "ID: {$tipo->id} - {$tipo->nombre}\n";
}
