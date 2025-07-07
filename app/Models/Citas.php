<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Citas extends Model
{
    /** @use HasFactory<\Database\Factories\CitasFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $table = 'citas';
    
     protected $fillable = [
        'medico_id',
        'paciente_id',
        'fecha',
        'hora',
        'motivo',
        'estado',
        'centro_id',
    ];

    public function paciente(){
        return $this->belongsTo(Pacientes::class, 'paciente_id');
    }

    public function medico(){
        return $this->belongsTo(Medico::class, 'medico_id');
    }

        public function confirmar(): void
    {
        $this->update(['estado' => 'Confirmado']);
    }

    public function cancelar(): void
    {
        $this->update(['estado' => 'Cancelado']);
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
