<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped;

class Consulta extends ModeloBase
{
    /** @use HasFactory<\Database\Factories\ConsultaFactory> */
    use HasFactory;
    use SoftDeletes;
    use TenantScoped; // Assuming you have a trait for tenant scoping

    protected $table = 'consultas';
    protected $fillable = [
        'diagnostico',
        'tratamiento',
        'observaciones',
        'paciente_id',
        'medico_id',
        'centro_id',
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

    public function recetas()
    {
        return $this->hasMany(Receta::class, 'consulta_id');
    }
}
