<?php

namespace App\Models\ContabilidadMedica;

use App\Models\ModeloBase;
use App\Models\Centros_Medico;
use App\Models\FacturaDetalle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\Traits\TenantScoped;
use App\Models\ContabilidadMedica\LiquidacionHonorario;

class LiquidacionDetalle extends ModeloBase
{
    use HasFactory;
    use SoftDeletes;
    use TenantScoped;

    protected $table = 'liquidaciones_detalles';

    protected $fillable = [
        'liquidacion_id',
        'factura_detalle_id',
        'porcentaje_honorario',
        'monto_honorario',
        'centro_id',
    ];

    public function liquidacion(): BelongsTo
    {
        return $this->belongsTo(LiquidacionHonorario::class, 'liquidacion_id');
    }

    public function facturaDetalle(): BelongsTo
    {
        return $this->belongsTo(FacturaDetalle::class, 'factura_detalle_id');
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }
}
