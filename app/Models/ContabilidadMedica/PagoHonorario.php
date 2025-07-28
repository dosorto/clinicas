<?php

namespace App\Models\ContabilidadMedica;

use App\Models\ModeloBase;
use App\Models\Centros_Medico;
use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class PagoHonorario extends ModeloBase
{
    use HasFactory;
    use SoftDeletes;
    use TenantScoped;

    protected $table = 'pagos_honorarios';

    protected $fillable = [
        'liquidacion_id',
        'fecha_pago',
        'monto_pagado',
        'metodo_pago',
        'referencia_bancaria',
        'retencion_isr_pct',
        'retencion_isr_monto',
        'observaciones',
        'centro_id',
    ];

    public function liquidacion(): BelongsTo
    {
        return $this->belongsTo(LiquidacionHonorario::class, 'liquidacion_id');
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }
}