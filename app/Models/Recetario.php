<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\TenantScoped; // Assuming you have a trait for recetario specific methods
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recetario extends ModeloBase
{
    /** @use HasFactory<\Database\Factories\RecetarioFactory> */
    use HasFactory;
    use TenantScoped; // Assuming you have a trait for recetario specific methods

    protected $table = 'recetarios';

    protected $fillable = [
        'medico_id',
        'consulta_id',
        'centro_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function consulta()
    {
        return $this->belongsTo(Consulta::class, 'consulta_id');
    }

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }
    public function recetas()
    {
        return $this->hasMany(Receta::class, 'recetario_id');
    }

    public function centro()
    {
        return $this->belongsTo(CentroMedico::class, 'centro_id');
    }

    // Accessor para datos del paciente (muy útil)
    public function getDatosPacienteAttribute(): ?object
    {
        if (!$this->consulta || !$this->consulta->paciente) {
            return null;
        }

        $paciente = $this->consulta->paciente;
        $persona = $paciente->persona;
        
        return (object) [
            'id' => $paciente->id,
            'nombre_completo' => trim("{$persona->primer_nombre} {$persona->segundo_nombre} {$persona->primer_apellido} {$persona->segundo_apellido}"),
            'dni' => $persona->dni,
            'telefono' => $persona->telefono,
            'edad' => $persona->fecha_nacimiento?->age ?? null,
            'sexo' => $persona->sexo,
        ];
    }

    // Accessor para datos del médico
    public function getDatosMedicoAttribute(): ?object
    {
        if (!$this->medico || !$this->medico->persona) {
            return null;
        }

        $persona = $this->medico->persona;
        
        return (object) [
            'nombre_completo' => trim("{$persona->primer_nombre} {$persona->segundo_nombre} {$persona->primer_apellido} {$persona->segundo_apellido}"),
            'especialidad' => $this->medico->especialidad,
            'dni' => $persona->dni,
        ];
    }

}
