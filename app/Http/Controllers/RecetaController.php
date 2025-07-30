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
            'medico.recetarios',      // Cambiado a plural para cargar todos los recetarios
            'consulta'
        ]);

        // También cargar las relaciones del médico específicamente para asegurar que se carguen
        $receta->medico->load(['especialidades', 'centro', 'recetarios']);


        // Buscar recetario por medico y consulta (si existe uno para esta consulta)
        $recetario = $receta->medico->recetarios()
            ->where('consulta_id', $receta->consulta_id)
            ->latest()
            ->first();
        // Si no existe, usar el más reciente del médico
        if (!$recetario) {
            $recetario = $receta->medico->recetarios()->latest()->first();
        }
        $config = $recetario ? $recetario->configuracion : [];

        return view('receta.imprimir', compact('receta', 'config'));
    }
}