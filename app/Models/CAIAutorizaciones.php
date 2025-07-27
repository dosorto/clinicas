<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\TenantScoped;

class CAIAutorizaciones extends ModeloBase
{
    use HasFactory, SoftDeletes, TenantScoped;
    protected $table = 'cai_autorizaciones';

    protected string $tenantKeyName = 'centro_id';

    protected $fillable = [
        'rtn',
        'cai_codigo',
        'cantidad',
        'rango_inicial',
        'rango_final',
        'numero_actual',
        'fecha_limite',
        'estado',
        'centro_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'fecha_limite' => 'date',
        'cantidad' => 'integer',
        'rango_inicial' => 'integer',
        'rango_final' => 'integer',
        'numero_actual' => 'integer',
    ];

    // Relaciones
    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    public function caiCorrelativos(): HasMany
    {
        return $this->hasMany(Cai_Correlativos::class, 'autorizacion_id');
    }

    // Métodos auxiliares
    public function esValida(): bool
    {
        return $this->estado === 'ACTIVA' 
            && $this->fecha_limite >= now()->toDateString()
            && $this->numero_actual <= $this->rango_final;
    }

    public function numerosDisponibles(): int
    {
        return max(0, $this->rango_final - $this->numero_actual + 1);
    }

    public function porcentajeUtilizado(): float
    {
        if ($this->cantidad <= 0) return 0;
        
        $utilizados = $this->numero_actual - $this->rango_inicial + 1;
        return ($utilizados / $this->cantidad) * 100;
    }

    public function obtenerSiguienteNumero(): ?int
    {
        if (!$this->esValida()) {
            return null;
        }

        if ($this->numero_actual > $this->rango_final) {
            $this->update(['estado' => 'AGOTADA']);
            return null;
        }

        return $this->numero_actual;
    }

    public function incrementarNumero(): bool
    {
        if (!$this->esValida()) {
            return false;
        }

        $this->increment('numero_actual');
        
        // Verificar si se agotó
        if ($this->numero_actual > $this->rango_final) {
            $this->update(['estado' => 'AGOTADA']);
        }

        return true;
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

        // Verificar fecha de vencimiento
        static::updating(function ($model) {
            if ($model->fecha_limite < now()->toDateString() && $model->estado === 'ACTIVA') {
                $model->estado = 'VENCIDA';
            }
        });
    }
}
