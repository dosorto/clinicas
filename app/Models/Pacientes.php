<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Traits\TenantScoped; 

class Pacientes extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($paciente) {
            if (empty($paciente->centro_id) && \Illuminate\Support\Facades\Auth::check()) {
                $paciente->centro_id = \Illuminate\Support\Facades\Auth::user()->centro_id;
            }
            if (\Illuminate\Support\Facades\Auth::check()) {
                $paciente->created_by = \Illuminate\Support\Facades\Auth::id();
            }
        });

        static::updating(function ($paciente) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $paciente->updated_by = \Illuminate\Support\Facades\Auth::id();
            }
        });

        static::deleting(function ($paciente) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $paciente->deleted_by = \Illuminate\Support\Facades\Auth::id();
                $paciente->save();
            }
        });

        static::addGlobalScope('centro', function ($query) {
     $user = \Illuminate\Support\Facades\Auth::user();
    
        if ($user && !$user->roles->contains('name', 'root')) {
        $query->where('centro_id', $user->centro_id);
         }
     });
    }

    protected $fillable = [
        'persona_id',
        'grupo_sanguineo',
        'contacto_emergencia',
        'centro_id', // multi-tenant
    ];

    /**
     * Scope para filtrar pacientes por el centro del usuario autenticado
     */
    public function scopeForCurrentUser($query)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user && $user->centro_id) {
            return $query->where('centro_id', $user->centro_id);
        }
        return $query;
    }

    public function centro()
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    public function persona(){
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Citas::class, 'paciente_id');
    }

    // Relación muchos a muchos con Enfermedade
    public function enfermedades()
    {
        return $this->belongsToMany(
            Enfermedade::class,
            'enfermedades_pacientes',
            'paciente_id',
            'enfermedad_id'
        )->withPivot(['fecha_diagnostico', 'tratamiento']);
    }
}