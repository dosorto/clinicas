<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\CentroScope;

class ModeloBase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'created_by',
        'deleted_by',
        'updated_by',
    ];

    protected static function boot()
    {
        parent::boot();

        // Agregar el scope global para filtrar por centro
        // Solo si la clase actual usa el trait HasCentro
        if (in_array('centro_id', (new static)->getFillable())) {
            static::addGlobalScope(new CentroScope);
        }

        static::creating(function ($model) {
            if (!$model->created_by && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->save();
            }
        });
    }

}