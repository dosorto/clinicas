<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped;

class FacturaConfiguracion extends ModeloBase
{
    /** @use HasFactory<\Database\Factories\FacturaConfiguracionFactory> */
    use HasFactory;
    use SoftDeletes;
    use TenantScoped; // Assuming you have a trait for tenant scoping
    protected $table = 'factura_configuraciones';

    protected $fillable = [
        'medico_id',
        'consulta_id',
        'centro_id',
        'logo',
        'razon_social',
        'telefono',
        'direccion',
        'color_primario',
        'color_secundario',
        'encabezado',
        'pie_pagina',
        'formato_numeracion',

    ];

    protected $casts = [
        'color_primario' => 'string',
        'color_secundario' => 'string',
    ];

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }
    public function consulta()
    {
        return $this->belongsTo(Consulta::class, 'consulta_id');
    }
    public function centro()
    {
        return $this->belongsTo(CentroMedico::class, 'centro_id');
        }

}
