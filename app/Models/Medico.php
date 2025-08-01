<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\Traits\TenantScoped; 

class Medico extends ModeloBase
{
    use HasFactory;
    use SoftDeletes;
    use TenantScoped; // Trait para el multi-tenant

protected $table = 'medicos';

    protected $fillable = [
        'persona_id',
        'numero_colegiacion',
        'horario_entrada',
        'horario_salida',
        'centro_id', // multi-tenant
    ];

    public function centro()
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    public function centrosMedicos()
    {
        return $this->belongsToMany(Centros_Medico::class, 'centros_medicos_medicos', 'medico_id', 'centro_medico_id');
    }

    public function persona() {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function consultas() {
    return $this->hasMany(Consulta::class, 'medico_id');
    }

    public function recetas() {
    return $this->hasMany(Receta::class, 'medico_id');
    }

    public function recetarios() {
        return $this->hasMany(Recetario::class, 'medico_id');
    }

    public function recetario() {
        return $this->hasOne(Recetario::class, 'medico_id')->latest();
    }

    public function especialidades()
    {
        return $this->belongsToMany(Especialidad::class, 'especialidad_medicos', 'medico_id', 'especialidad_id');
    }
    
    public function contratos()
    {
        return $this->hasMany(\App\Models\ContabilidadMedica\ContratoMedico::class, 'medico_id');
    }

    public function contratoActivo()
    {
        return $this->hasOne(\App\Models\ContabilidadMedica\ContratoMedico::class, 'medico_id')
            ->where('activo', true)
            ->whereNull('fecha_fin')
            ->orWhere('fecha_fin', '>=', now())
            ->latest('fecha_inicio');
    }
    
    // Relaciones con contabilidad médica
    public function cargos()
    {
        return $this->hasMany(\App\Models\ContabilidadMedica\CargoMedico::class, 'medico_id');
    }
    
    public function liquidaciones()
    {
        return $this->hasMany(\App\Models\ContabilidadMedica\LiquidacionHonorario::class, 'medico_id');
    }
    
    public function pagos()
    {
        return $this->hasManyThrough(
            \App\Models\ContabilidadMedica\PagoHonorario::class,
            \App\Models\ContabilidadMedica\LiquidacionHonorario::class,
            'medico_id', // Foreign key en liquidaciones_honorarios
            'liquidacion_id', // Foreign key en pagos_honorarios
            'id', // Local key en medicos
            'id' // Local key en liquidaciones_honorarios
        );
    }
    
    // Atributos calculados
    public function getNombreCompletoAttribute()
    {
        if ($this->persona) {
            return $this->persona->nombre . ' ' . $this->persona->apellido;
        }
        
        return 'Médico #' . $this->id;
    }
    
    public function getEspecialidadAttribute()
    {
        return $this->especialidades->first();
    }
    
    // Relación singular para obtener la especialidad principal
    public function especialidad()
    {
        return $this->belongsToMany(Especialidad::class, 'especialidad_medicos', 'medico_id', 'especialidad_id')
                    ->take(1);
    }
    
    // Método para obtener la primera especialidad
    public function especialidadPrincipal()
    {
        return $this->especialidades()->first();
    }
}
