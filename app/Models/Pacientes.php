<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pacientes extends Model
{
    /** @use HasFactory<\Database\Factories\PacientesFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'persona_id',
        'grupo_sanguineo',
        'contacto_emergencia',
    ];

    public function persona(){
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Citas::class, 'paciente_id');
    }

    public function consultas(): HasMany
    {
        return $this->hasMany(Consulta::class, 'paciente_id');
    }

    public function examenes(): HasMany
    {
        return $this->hasMany(Examenes::class, 'paciente_id');
    }

    public function recetas(): HasMany
    {
        return $this->hasMany(Receta::class, 'paciente_id');
    }

    // CORRECCIÓN: Nombre correcto de la tabla y campos pivot
    public function enfermedades(): BelongsToMany
    {
        return $this->belongsToMany(Enfermedade::class, 'enfermedades_pacientes', 'paciente_id', 'enfermedad_id')
                    ->withPivot('fecha_diagnostico', 'tratamiento', 'created_by', 'updated_by', 'deleted_by')
                    ->withTimestamps();
    }
}