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

    public function imprimirPorConsulta(\App\Models\Consulta $consulta)
    {
        // Cargar todas las recetas de la consulta con sus relaciones
        $recetas = $consulta->recetas()->with([
            'paciente.persona',
            'medico.persona',
            'medico.especialidades',
            'medico.centro',
            'medico.recetario',
            'consulta'
        ])->get();

        if ($recetas->isEmpty()) {
            abort(404, 'No hay recetas asociadas a esta consulta.');
        }

        // Obtener la configuración del recetario del médico (usando la primera receta como referencia)
        $primeraReceta = $recetas->first();
        $recetario = $primeraReceta->medico->recetario ?? null;
        $config = $recetario ? $recetario->configuracion : [];

        return view('receta.imprimir-consulta', compact('recetas', 'consulta', 'config'));
    }
}
