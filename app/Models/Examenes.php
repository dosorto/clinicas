<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped; 

class Examenes extends ModeloBase
{
    /** @use HasFactory<\Database\Factories\ExamenesFactory> */
    use HasFactory;
    use SoftDeletes;
    use TenantScoped; // Assuming you have a trait for tenant scoping

    protected $table = 'examenes';
    protected $fillable = [
        'paciente_id',
        'consulta_id',
        'medico_id',
        'descripcion',
        'url_archivo',
        'fecha_resultado',
        'centro_id',
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
