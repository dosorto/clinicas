<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\FacturaPdfController;
use App\Http\Controllers\NominaController;
use App\Models\{Consulta, FacturaDetalle};

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/receta/{receta}/imprimir', [RecetaController::class, 'imprimir'])->name('receta.imprimir');

// Rutas para PDFs de facturas
Route::get('/factura/{factura}/pdf', [FacturaPdfController::class, 'generarPdf'])->name('factura.pdf');
Route::get('/factura/{factura}/pdf/preview', [FacturaPdfController::class, 'previewPdf'])->name('factura.pdf.preview');
Route::post('/factura/{factura}/pdf/guardar', [FacturaPdfController::class, 'guardarPdf'])->name('factura.pdf.guardar');
Route::get('/facturas/pdf/lote', [FacturaPdfController::class, 'generarPdfLote'])->name('facturas.pdf.lote');


// Rutas para imprimir recetas
Route::get('/receta/{receta}/imprimir', [RecetaController::class, 'imprimir'])->name('recetas.imprimir');
Route::get('/consulta/{consulta}/recetas/imprimir', [RecetaController::class, 'imprimirPorConsulta'])->name('recetas.imprimir.consulta');

// Rutas para nóminas
Route::get('/nomina/{nomina}/pdf', [NominaController::class, 'generarPDFNomina'])->name('nomina.pdf');

// API para obtener totales de consulta (usado por crear factura)
Route::get('/api/consultas/{consulta}/totales', function ($consultaId) {
    $servicios = FacturaDetalle::where('consulta_id', $consultaId)
        ->whereNull('factura_id')
        ->get();
    
    return response()->json([
        'subtotal' => $servicios->sum('subtotal'),
        'impuesto_total' => $servicios->sum('impuesto_monto'),
        'total' => $servicios->sum('total_linea'),
    ]);
});
