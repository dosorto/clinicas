<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped; // Assuming you have a TenantScope trait for tenant scopi

class Persona extends Model
{
    /** @use HasFactory<\Database\Factories\PersonaFactory> */
    use HasFactory;
    use SoftDeletes;
    use TenantScoped; // Assuming you have a TenanScope trait for tenant scoping

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
        'centro_id',
        
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
   
    Public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }
}
