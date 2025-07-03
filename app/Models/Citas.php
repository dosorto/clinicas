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
    ];

    public function paciente(){
        return $this->belongsTo(Pacientes::class, 'paciente_id');
    }

    public function medico(){
        return $this->belongsTo(Medico::class, 'medico_id');
    }
}
