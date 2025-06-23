<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enfermedades_Paciente extends Model
{
    /** @use HasFactory<\Database\Factories\EnfermedadesPacienteFactory> */
    use HasFactory;
    use SoftDeletes;

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
