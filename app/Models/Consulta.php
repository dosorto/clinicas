<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consulta extends Model
{
    /** @use HasFactory<\Database\Factories\ConsultaFactory> */
    use HasFactory;

    protected $table = 'consultas';
    protected $fillable = [
        'diagnostico',
        'tratamiento',
        'observaciones',
    ];

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
