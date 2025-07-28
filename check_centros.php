<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Centros_Medico;

echo "=== Centros Médicos ===\n";
$centros = Centros_Medico::take(10)->get(['id', 'nombre_centro']);

foreach ($centros as $centro) {
    echo "ID: {$centro->id} - Nombre: {$centro->nombre_centro}\n";
}

echo "\nVerificando centro con ID 1:\n";
$centro1 = Centros_Medico::find(1);
if ($centro1) {
    echo "Centro ID 1 encontrado: {$centro1->nombre_centro}\n";
} else {
    echo "Centro con ID 1 NO ENCONTRADO\n";
}
