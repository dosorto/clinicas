<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Centros_Medicos_Medico extends Model
{
    /** @use HasFactory<\Database\Factories\CentrosMedicosMedicoFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $table = 'centros_medicos_medicos';

    protected $fillable = [
        'medico_id',
        'centro_medico_id',
        'horario_entrada',
        'horario_salida',
        'centro_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];


    public function centro_medico(){
        return $this->belongsTo(Centros_Medico::class, 'centro_medico_id');
    }

    public function medico(){
        return $this->belongsTo(Medico::class, 'medico_id');
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
