<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\NominaController;

Route::get('/', function () {
    return redirect('/admin');
});

// Rutas para imprimir recetas
Route::get('/receta/{receta}/imprimir', [RecetaController::class, 'imprimir'])->name('recetas.imprimir');
Route::get('/consulta/{consulta}/recetas/imprimir', [RecetaController::class, 'imprimirPorConsulta'])->name('recetas.imprimir.consulta');

// Rutas para nóminas
Route::get('/nomina/{nomina}/pdf', [NominaController::class, 'generarPDFNomina'])->name('nomina.pdf');
