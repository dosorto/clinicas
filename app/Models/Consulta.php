<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consulta extends Model
{
    /** @use HasFactory<\Database\Factories\ConsultaFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $table = 'consultas';
    protected $fillable = [
        'diagnostico',
        'tratamiento',
        'observaciones',
        'paciente_id',
        'medico_id',
        'cita_id',
        'centro_id',
    ];

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

    public function paciente()
    {
        return $this->belongsTo(Pacientes::class, 'paciente_id');
    }
    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }
    public function cita()
    {
        return $this->belongsTo(Citas::class, 'cita_id');
    }
}
