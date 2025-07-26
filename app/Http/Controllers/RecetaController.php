<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Receta;

class RecetaController extends Controller
{
    public function imprimir(Receta $receta)
    {
        // Cargar todas las relaciones necesarias incluyendo las tablas pivote
        $receta->load([
            'paciente.persona',
            'medico.persona',
            'medico.especialidades',  // Relación many-to-many con tabla pivote
            'medico.centro',          // Relación belongsTo para el centro principal
            'medico.recetario',
            'consulta'
        ]);

        // También cargar las relaciones del médico específicamente para asegurar que se carguen
        $receta->medico->load(['especialidades', 'centro']);

        // Obtener la configuración del recetario del médico
        $recetario = $receta->medico->recetario ?? null;
        $config = $recetario ? $recetario->configuracion : [];

        return view('receta.imprimir', compact('receta', 'config'));
    }
}