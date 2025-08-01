<?php

namespace App\Models\ContabilidadMedica;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\Traits\TenantScoped;
use App\Models\Medico;
use App\Models\Centros_Medico;
use App\Models\ModeloBase;
use App\Models\ContabilidadMedica\LiquidacionDetalle;
use App\Models\ContabilidadMedica\PagoHonorario;
use App\Models\ContabilidadMedica\ContratoMedico;



class LiquidacionHonorario extends ModeloBase
{
    use HasFactory;
    use SoftDeletes;
    use TenantScoped;

    protected $table = 'liquidaciones_honorarios';

    protected $fillable = [
        'medico_id',
        'contrato_medico_id',
        'periodo_inicio',
        'periodo_fin',
        'servicios_brutos',
        'porcentaje_medico',
        'monto_total',
        'deducciones',
        'estado',
        'fecha_liquidacion',
        'observaciones',
        'tipo_liquidacion',
        'centro_id',
        'created_by',
        'updated_by',
    ];

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }

    public function contratoMedico(): BelongsTo
    {
        return $this->belongsTo(ContratoMedico::class, 'contrato_medico_id');
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(LiquidacionDetalle::class, 'liquidacion_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagoHonorario::class, 'liquidacion_id');
    }
}