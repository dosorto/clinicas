<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped;

class Centros_Medico extends Model
{
    /** @use HasFactory<\Database\Factories\CentrosMedicoFactory> */
    use HasFactory;
    use SoftDeletes;
    use TenantScoped;


    protected $table = 'centros_medicos';

    protected $fillable = [
        'nombre_centro',
        'direccion',
        'telefono',
        'fotografia',
    ];

    public function centro_medico_medico() {
        return $this->hasMany(Centros_Medicos_Medico::class, 'centro_medico_id');
    }
    
    public function medicos()
    {
        return $this->hasMany(
            \App\Models\Centros_Medicos_Medico::class,
            'centro_medico_id'
        );
    }

    protected static function booted()
    {
        parent::booted();
        static::creating(function ($model) {
            if (auth()->check()) {
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
