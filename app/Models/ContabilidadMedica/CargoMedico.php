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
use App\Models\ContabilidadMedica\ContratoMedico;
use App\Models\ContabilidadMedica\PagoCargoMedico;


class CargoMedico extends ModeloBase
{
    use HasFactory;
    use SoftDeletes;
    use TenantScoped;

    protected $table = 'cargos_medicos';

    protected $fillable = [
        'medico_id',
        'contrato_id',
        'descripcion',
        'periodo_inicio',
        'periodo_fin',
        'subtotal',
        'impuesto_total',
        'total',
        'estado',
        'observaciones',
        'centro_id',
    ];

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(ContratoMedico::class, 'contrato_id');
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagoCargoMedico::class, 'cargo_id');
    }
}