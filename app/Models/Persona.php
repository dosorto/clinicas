<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Persona extends Model
{
    /** @use HasFactory<\Database\Factories\PersonaFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'dni',
        'telefono',
        'direccion',
        'sexo',
        'fecha_nacimiento',
        'nacionalidad_id',
        'fotografia',
        
    ];

    public function getNombreCompletoAttribute()
    {
    return $this->primer_nombre . ' ' . $this->primer_apellido;
    }

    public function nacionalidad(): BelongsTo
    {
        return $this->belongsTo(Nacionalidad::class);
    }

     public function paciente()
    {
        return $this->hasOne(Pacientes::class, 'persona_id');
    }

    public function medico(): HasOne
    {
        return $this->hasOne(Medico::class, 'persona_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'persona_id');
    }

    // Accessor para nombre completo
    public function getNombreCompletoAttribute(): string
    {
        return trim(
            $this->primer_nombre . ' ' . 
            $this->segundo_nombre . ' ' . 
            $this->primer_apellido . ' ' . 
            $this->segundo_apellido
        );
    }

}
