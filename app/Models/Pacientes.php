<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pacientes extends Model
{
    /** @use HasFactory<\Database\Factories\PacientesFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'grupo_sanguineo',
        'contacto_emergencia',
    ];

    public function persona(){
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    
}
