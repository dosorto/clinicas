<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

echo "Verificando modelo Pacientes...\n";

try {
    $count = App\Models\Pacientes::count();
    echo "Pacientes encontrados: $count\n";
    echo "El modelo funciona correctamente!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
