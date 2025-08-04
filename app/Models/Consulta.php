<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped; 

class Consulta extends ModeloBase
{
    /** @use HasFactory<\Database\Factories\ConsultaFactory> */
    use HasFactory;
    use SoftDeletes;
    use TenantScoped; // Assuming you have a trait for tenant scoping

    protected $table = 'consultas';
    protected $fillable = [
        'diagnostico',
        'tratamiento',
        'observaciones',
        'paciente_id',
        'medico_id',
        'cita_id',
        'centro_id',
    ];

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class);
    }
    
    public function cita()
    {
        return $this->belongsTo(Citas::class, 'cita_id');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Pacientes::class, 'paciente_id'); // Verificar que el campo sea correcto
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class, 'medico_id'); // Verificar que el campo sea correcto
    }
    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }
    // AGREGAR ESTA RELACIÓN:
    public function servicios(): HasMany
    {
        return $this->hasMany(FacturaDetalle::class, 'consulta_id')
                    ->whereNull('factura_id');
    }
    
    // O si prefieres llamarla detallesTemporales:
    public function detallesTemporales(): HasMany
    {
        return $this->hasMany(FacturaDetalle::class, 'consulta_id')
                    ->whereNull('factura_id');
    }
}
