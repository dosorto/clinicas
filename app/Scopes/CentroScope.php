<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CentroScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // Solo aplicar si hay un usuario autenticado y el modelo tiene la columna centro_id
        if (auth()->check() && in_array('centro_id', $model->getFillable())) {
            // Obtener el centro_id actual
            $centroId = session('current_centro_id');
            
            // Si es root y no hay centro seleccionado, no aplicar filtro
            if (auth()->user()->hasRole('root') && !$centroId) {
                return;
            }
            
            // Para root, usar el centro seleccionado
            // Para otros usuarios, usar su centro asignado
            $centroIdToFilter = auth()->user()->hasRole('root') 
                ? $centroId 
                : auth()->user()->centro_id;

            if ($centroIdToFilter) {
                $builder->where($model->getTable() . '.centro_id', $centroIdToFilter);
            }
        }
    }
}
