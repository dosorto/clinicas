<?php

namespace App\Models;

use App\Services\CaiNumerador;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\SoftDeletes;

class Factura extends Model
{
    use HasFactory, SoftDeletes;

    /* ─────────────────────────  ATRIBUTOS  ───────────────────────── */
    protected $fillable = [
        'paciente_id',
        'cita_id',
        'consulta_id',
        'medico_id',
        'fecha_emision',
        'subtotal',
        'descuento_total',
        'impuesto_total',
        'total',
        'estado',
        'observaciones',
        'cai_autorizacion_id',
        'centro_id',
        'descuento_id',
        'tipo_pago_id',
        'cai_correlativo_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = ['fecha_emision','created_at','updated_at','deleted_at'];

    protected $casts = [
        'fecha_emision'   => 'date',
        'subtotal'        => 'decimal:2',
        'descuento_total' => 'decimal:2',
        'impuesto_total'  => 'decimal:2',
        'total'           => 'decimal:2',
    ];

    /* ────────────────────────  RELACIONES  ─────────────────────── */
    public function paciente() : BelongsTo { return $this->belongsTo(Pacientes::class); }
    public function descuento(): BelongsTo { return $this->belongsTo(Descuento::class); }
    public function cita()     : BelongsTo { return $this->belongsTo(Citas::class); }
    public function consulta() : BelongsTo { return $this->belongsTo(Consulta::class); }
    public function medico()   : BelongsTo { return $this->belongsTo(Medico::class); }
    public function centro()   : BelongsTo { return $this->belongsTo(Centros_Medico::class,'centro_id'); }
    public function detalles() : HasMany   { return $this->hasMany(FacturaDetalle::class); }
    public function pagos()    : HasMany   { return $this->hasMany(Pagos_Factura::class); }
    public function caiCorrelativo(): BelongsTo
    {
        return $this->belongsTo(CAI_Correlativos::class,'cai_correlativo_id');
    }

    /* Nº de factura virtual */
    public function getNumeroFacturaAttribute(): ?string
    {
        return $this->caiCorrelativo?->numero_factura;
    }

    /* ─────────────────────────  EVENTOS  ───────────────────────── */
    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (self $factura) {

            if (! empty($factura->usa_cai)) {       // Usa CAI
                /* lógica existente de correlativo */
            } else {
                $factura->cai_correlativo_id  = null;
                $factura->cai_autorizacion_id = null;
            }

            // centro y auditoría
            if (auth()->check()) {
                $factura->centro_id  ??= auth()->user()->centro_id;
                $factura->created_by ??= auth()->id();
            }

            // CAI activo
            $cai = CAIAutorizaciones::query()
                ->where('centro_id', $factura->centro_id)
                ->where('estado', 'ACTIVA')
                ->whereDate('fecha_limite', '>=', now())
                ->orderBy('id')
                ->firstOrFail();

            $corr = CaiNumerador::generar(
                caiId     : $cai->id,
                usuarioId : auth()->id(),
                centroId  : $factura->centro_id,
            );

            $factura->cai_correlativo_id  = $corr->id;
            $factura->cai_autorizacion_id = $cai->id;

            static::created(function ($pago) {
                $factura = $pago->factura;
                if ($factura) $factura->actualizarEstadoPago();
            });
        });
    }

    /* ─────────────────────  MÉTODOS DE PAGO  ───────────────────── */
    public function montoPagado(): float
    {
        return $this->pagos()->where('estado','CONFIRMADO')->sum('monto_recibido');
    }

    public function saldoPendiente(): float
    {
        return (float) $this->total - $this->montoPagado();
    }

    public function afterCommit(): void
    {
        // Al crear factura se abre cuenta por cobrar si total > 0
        if ($this->wasRecentlyCreated && $this->total > 0) {
            Cuentas_Por_Cobrar::create([
                'factura_id'    => $this->id,
                'paciente_id'   => $this->paciente_id,
                'saldo_pendiente'=> $this->total,
                'fecha_vencimiento'=> now()->addDays(30),
                'centro_id'     => $this->centro_id,
                'estado_cuentas_por_cobrar' => 'ABIERTA',
                'created_by'    => $this->created_by,
            ]);
        }
    }

    public function actualizarEstadoPago(): void
    {
        $pagado = $this->montoPagado();

        $this->estado = match (true) {
            $pagado == 0            => 'PENDIENTE',
            $pagado >= $this->total => 'PAGADA',
            default                 => 'PARCIAL',
        };

        $this->save();
    }
}
