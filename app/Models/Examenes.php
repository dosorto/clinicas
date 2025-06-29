<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Examenes extends Model
{
    /** @use HasFactory<\Database\Factories\ExamenesFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $table = 'examenes';

    protected $fillable = [
        'descripcion',
        'url_archivo',
        'fecha_resultado',
    ];

    public function paciente()
    {
        return $this->belongsTo(Pacientes::class, 'paciente_id');
    }

    public function consulta()
    {
        return $this->belongsTo(Consulta::class, 'consulta_id');
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }
}
