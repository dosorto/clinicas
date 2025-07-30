<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecetaController;

Route::get('/', function () {
    return redirect('/admin');
});

// Ruta para imprimir factura
use App\Http\Controllers\FacturaController;
Route::get('/factura/{factura}/imprimir', [FacturaController::class, 'imprimir'])->name('facturas.imprimir');

// Rutas para imprimir recetas
Route::get('/receta/{receta}/imprimir', [RecetaController::class, 'imprimir'])->name('recetas.imprimir');
Route::get('/consulta/{consulta}/recetas/imprimir', [RecetaController::class, 'imprimirPorConsulta'])->name('recetas.imprimir.consulta');
