<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Traits\TenantScoped; 

class Pacientes extends ModeloBase
{
    use HasFactory, SoftDeletes;
    use TenantScoped; // Trait para el multi-tenant

    protected static function booted()
    {
        parent::booted();

        // El trait TenantScoped ya maneja:
        // - Asignación automática de centro_id al crear
        // - Global scope para filtrar por centro
        // - Bypass para usuario root
        
        // No necesitamos lógica adicional específica para este modelo
        // La auditoría (created_by, updated_by, deleted_by) se maneja en ModeloBase
    }

    protected $fillable = [
        'persona_id',
        'grupo_sanguineo',
        'contacto_emergencia',
        'centro_id', // multi-tenant
        'created_by',
        'updated_by',
        'deleted_by',
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

    public function consultas(): HasMany
    {
        return $this->hasMany(Consulta::class, 'paciente_id');
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