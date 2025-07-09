<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    protected $table = 'tenants';
    protected $fillable = [
        'centro_id',
        'name',
        'domain',
        'database',
    ];

    public static function booted()
    {
        parent::booted();

        // Asegurar que el tenant actual siempre esté disponible después de crear uno nuevo
        static::created(function (self $tenant) {
            if (!static::current()) {
                $tenant->makeCurrent();
            }
        });
    }

    public function centro()
    {
        return $this->belongsTo(\App\Models\Centros_Medico::class, 'centro_id');
    }

    /**
     * Obtiene o crea un tenant para un centro médico
     */
    public static function findOrCreateForCentro(Centros_Medico $centro): self
    {
        $name = $centro->nombre_centro;
        $domain = strtolower(str_replace(' ', '-', $name)) . '.' . config('app.domain', 'localhost');
        
        return static::firstOrCreate(
            ['centro_id' => $centro->id],
            [
                'name' => $name,
                'domain' => $domain,
                'database' => 'clinica_' . $centro->id, // Base de datos por defecto
            ]
        );
    }
}
