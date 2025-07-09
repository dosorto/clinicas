<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped; 

class Medico extends Model
{
    use HasFactory;
    use SoftDeletes;
    use TenantScoped; // Assuming you have a trait for tenant scoping

protected $table = 'medicos';

    protected $fillable = [
        'persona_id',
        'numero_colegiacion',
        'centro_id',
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
    protected static function booted()
    {
        parent::booted();
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->save();
            }
        });
    }
}
