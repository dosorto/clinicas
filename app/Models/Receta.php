<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped; 

class Receta extends ModeloBase
{
    /** @use HasFactory<\Database\Factories\RecetaFactory> */
    use HasFactory;
    use SoftDeletes;
    use TenantScoped; // Assuming you have a trait for tenant scoping

    protected $table = 'recetas';

    protected $fillable = [
        'medicamentos',
        'indicaciones',
        'paciente_id',
        'consulta_id',
        'medico_id',
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
