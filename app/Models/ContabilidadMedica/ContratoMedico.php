<?php

namespace App\Models\ContabilidadMedica;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\Traits\TenantScoped;
use App\Models\ModeloBase;
use App\Models\Medico;
use App\Models\Centros_Medico;
use Illuminate\Database\Eloquent\Builder;


class ContratoMedico extends ModeloBase
{
    use HasFactory;
    use SoftDeletes;
    use TenantScoped;
    
    protected static function booted()
    {
        parent::booted();
        
        static::addGlobalScope('relaciones', function (Builder $builder) {
            $builder->with(['centro', 'medico.persona']);
        });
    }

    protected $table = 'contratos_medicos';

    protected $fillable = [
        'medico_id',
        'salario_quincenal',
        'salario_mensual',
        'porcentaje_servicio',
        'fecha_inicio',
        'fecha_fin',
        'activo',
        'observaciones',
        'centro_id',
    ];

    protected $casts = [
        'porcentaje_servicio' => 'decimal:2',
        'activo' => 'boolean',
    ];

    protected $attributes = [
        'porcentaje_servicio' => 0,
    ];

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    // public function cargos(): HasMany
    // {
    //     return $this->hasMany(CargoMedico::class, 'contrato_id');
    // }
}