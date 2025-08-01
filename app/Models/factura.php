<?php

namespace App\Models;

use App\Models\ContabilidadMedica\CargoMedico;
use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Factura extends Model
{
    use HasFactory, SoftDeletes, TenantScoped;

    protected $table = 'facturas';

    protected $fillable = [
        'paciente_id',
        'medico_id',
        'cita_id',
        'numero_factura',
        'fecha',
        'subtotal',
        'impuesto',
        'total',
        'estado',
        'metodo_pago',
        'referencia_pago',
        'observaciones',
        'centro_id',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'subtotal' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Citas::class, 'cita_id');
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(FacturaDetalle::class, 'factura_id');
    }

    public function cargosMedicos(): HasMany
    {
        return $this->hasMany(CargoMedico::class, 'factura_id');
    }
}
