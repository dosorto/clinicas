<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medico extends Model
{
    use HasFactory;
    use SoftDeletes;

protected $table = 'medicos';

    protected $fillable = [
        'numero_colegiacion',
    ];

    public function persona() {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function consultas() {
    return $this->hasMany(Consulta::class, 'medico_id');
    }

    public function recetas() {
    return $this->hasMany(Receta::class, 'medico_id');
    }

    public function especialidades()
    {
    return $this->belongsToMany(Especialidad::class, 'especialidad_medicos', 'medico_id', 'especialidad_id');
    }
   
}
