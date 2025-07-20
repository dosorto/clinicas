<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Persona extends ModeloBase
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
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected static function booted()
    {
        parent::booted();
        
        // Validación de DNI único (funcionalidad específica del modelo Persona)
        static::saving(function ($model) {
            $query = static::where('dni', $model->dni);
            
            if ($model->exists) {
                $query->where('id', '!=', $model->id);
            }
            
            if ($query->exists()) {
                throw new \Illuminate\Validation\ValidationException(
                    validator([], []), 
                    ['dni' => 'El DNI ya está en uso.']
                );
            }
        });
    }

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
   
    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }
}
