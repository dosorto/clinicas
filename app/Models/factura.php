<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\CAIAutorizaciones;
use App\Models\Pago_Factura;

class Factura extends Model
{
    use HasFactory, SoftDeletes;

    protected string $tenantKeyName = 'centro_id';

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
        'centro_id',
        'numero_factura',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'subtotal' => 'decimal:2',
        'descuento_total' => 'decimal:2',
        'impuesto_total' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relaciones
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Pacientes::class);
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Citas::class);
    }

    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class);
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class);
    }

    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(FacturaDetalle::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pagos_Factura::class);
    }

    // Métodos auxiliares
    public function calcularTotales(): void
    {
        $this->subtotal = $this->detalles()->sum('subtotal');
        $this->impuesto_total = $this->detalles()->sum('impuesto');
        $this->total = $this->subtotal + $this->impuesto_total - $this->descuento_total;
        $this->save();
    }

    public function generarNumeroFactura(): string
    {
        // Obtener CAI activo
        $cai = CAIAutorizaciones::where('centro_id', $this->centro_id)
            ->where('estado', 'ACTIVA')
            ->where('fecha_limite', '>=', now())
            ->first();

        if (!$cai) {
            throw new \Exception('No hay autorización CAI activa para generar facturas');
        }

        $numeroFactura = $cai->obtenerSiguienteNumero();
        if (!$numeroFactura) {
            throw new \Exception('No hay números disponibles en la autorización CAI');
        }

        $this->numero_factura = $numeroFactura;
        $this->save();

        // Incrementar el número actual en CAI
        $cai->incrementarNumero();

        return str_pad($numeroFactura, 8, '0', STR_PAD_LEFT);
    }

    public function montoPagado(): float
    {
        return $this->pagos()->where('estado', 'CONFIRMADO')->sum('monto');
    }

    public function saldoPendiente(): float
    {
        return $this->total - $this->montoPagado();
    }

    public function actualizarEstadoPago(): void
    {
        $montoPagado = $this->montoPagado();
        
        if ($montoPagado == 0) {
            $this->estado = 'PENDIENTE';
        } elseif ($montoPagado >= $this->total) {
            $this->estado = 'PAGADA';
        } else {
            $this->estado = 'PARCIAL';
        }
        
        $this->save();
    }

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function ($model) {
            if (auth()->check() && empty($model->centro_id)) {
                $user = auth()->user();
                if ($user && isset($user->centro_id)) {
                    $model->centro_id = $user->centro_id;
                }
            }
        });

        static::created(function ($model) {
            // Auto-generar número de factura después de crear
            if (empty($model->numero_factura)) {
                $model->generarNumeroFactura();
            }
        });
    }
}
