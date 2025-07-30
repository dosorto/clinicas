<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Factura;
use App\Models\FacturaConfiguracion;

class FacturaController extends Controller
{
    public function imprimir($factura)
    {
        $factura = Factura::with([
            'medico.persona',
            'centro',
            'paciente',
            'detalles.servicio',
            'detalles.descuento',
            'detalles.impuesto',
            'pagos',
            'caiCorrelativo',
        ])->findOrFail($factura);

        $config = FacturaConfiguracion::where(function($q) use ($factura) {
            $q->where('medico_id', $factura->medico_id)
              ->orWhere('centro_id', $factura->centro_id)
              ->orWhereNull('medico_id');
        })->orderByRaw('medico_id IS NOT NULL DESC, centro_id IS NOT NULL DESC')->first();

        return view('factura.imprimir', compact('factura', 'config'));
    }
}
