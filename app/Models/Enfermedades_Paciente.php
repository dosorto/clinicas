<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enfermedades_Paciente extends Model
{
    /** @use HasFactory<\Database\Factories\EnfermedadesPacienteFactory> */
    use HasFactory;

    protected $table = 'enfermedades__pacientes';

    public function paciente()
    {
        return $this->belongsTo(Pacientes::class, 'paciente_id');
    }

    public function enfermedad()
    {
        return $this->belongsTo(Enfermedade::class, 'enfermedad_id');
    }
}
