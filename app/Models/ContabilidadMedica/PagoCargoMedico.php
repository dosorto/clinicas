<?php

namespace App\Models\ContabilidadMedica;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\Traits\TenantScoped;
use App\Models\ModeloBase;
use App\Models\Centros_Medico;
use App\Models\ContabilidadMedica\CargoMedico;
class PagoCargoMedico extends ModeloBase
{
    use HasFactory;
    use SoftDeletes;
    use TenantScoped;

    protected $table = 'pagos_cargos_medicos';

    protected $fillable = [
        'cargo_id',
        'fecha_pago',
        'monto_pagado',
        'metodo_pago',
        'referencia',
        'observaciones',
        'centro_id',
    ];

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(CargoMedico::class, 'cargo_id');
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }
}
