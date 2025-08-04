<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturaDetalle extends Model
{
    protected $table = 'factura_detalles';

    protected $fillable = [
        'factura_id',
        'servicio_id',
        'consulta_id',
        'cantidad',
        'descuento_id',
        'subtotal',
        'impuesto_id',
        'impuesto_monto',
        'descuento_monto',
        'total_linea',
        'centro_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'descuento_monto' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total_linea' => 'decimal:2',
        'impuesto_monto' => 'decimal:2',
    ];

    /**
     * Accessor para calcular el precio unitario
     */
    public function getPrecioUnitarioAttribute(): float
    {
        if ($this->cantidad && $this->cantidad > 0) {
            return round($this->subtotal / $this->cantidad, 2);
        }
        
        // Si no hay cantidad o es 0, intentar obtener del servicio
        if ($this->servicio && $this->servicio->precio_unitario) {
            return $this->servicio->precio_unitario;
        }
        
        return 0.00;
    }

    /**
     * Accessor para obtener el total con impuestos
     */
    public function getTotalAttribute(): float
    {
        return $this->total_linea ?? 0.00;
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class);
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function consulta(): BelongsTo
    {
        return $this->belongsTo(Consulta::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
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
    }

    /**
     * Crear detalles de factura desde examenes de una consulta
     */
    public static function crearDesdeConsulta(int $consultaId, int $facturaId): void
    {
        $consulta = \App\Models\Consulta::with(['examenes'])->find($consultaId);
        
        if ($consulta && $consulta->examenes) {
            foreach ($consulta->examenes as $examen) {
                // Buscar el servicio correspondiente
                $servicio = null;
                
                if ($examen instanceof \App\Models\Servicio) {
                    $servicio = $examen;
                } else {
                    // Si es un examen, buscar el servicio relacionado
                    $servicio = \App\Models\Servicio::find($examen->servicio_id ?? $examen->id);
                }
                
                if ($servicio) {
                    // Calcular totales
                    $cantidad = 1;
                    $precio = $servicio->precio_unitario;
                    $subtotal = $precio * $cantidad;
                    
                    // Calcular impuesto si aplica
                    $impuestoMonto = 0;
                    if ($servicio->impuesto_id && !$servicio->es_exonerado) {
                        $impuesto = $servicio->impuesto;
                        if ($impuesto) {
                            $impuestoMonto = ($subtotal * $impuesto->porcentaje) / 100;
                        }
                    }
                    
                    $totalLinea = $subtotal + $impuestoMonto;
                    
                    self::create([
                        'factura_id' => $facturaId,
                        'servicio_id' => $servicio->id,
                        'consulta_id' => $consultaId,
                        'cantidad' => $cantidad,
                        'subtotal' => $subtotal,
                        'impuesto_id' => $servicio->impuesto_id,
                        'impuesto_monto' => $impuestoMonto,
                        'descuento_monto' => 0,
                        'total_linea' => $totalLinea,
                    ]);
                }
            }
        }
    }
}
