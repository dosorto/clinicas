<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\TenantScoped; 

/**
 * Ejemplo de cómo usar el patrón refactorizado
 * 
 * Este modelo hereda de ModeloBase que maneja:
 * - created_by, updated_by, deleted_by automáticamente
 * - SoftDeletes
 * 
 * Y usa TenantScoped trait que maneja:
 * - Asignación automática de centro_id
 * - Global scope para filtrar por centro
 * - Bypass para usuario root
 */
class EjemploModelo extends ModeloBase
{
    use HasFactory, SoftDeletes;
    use TenantScoped;

    protected static function booted()
    {
        parent::booted();

        // Aquí solo agregas lógica específica del modelo
        // Por ejemplo:
        
        static::creating(function ($modelo) {
            // Lógica específica al crear este modelo
            // Por ejemplo, generar un código único
            if (!$modelo->codigo) {
                $modelo->codigo = strtoupper(uniqid());
            }
        });

        static::updating(function ($modelo) {
            // Lógica específica al actualizar
            // Por ejemplo, validaciones especiales
        });

        // No necesitas duplicar la lógica de auditoría ni de tenant
        // Ya está manejada por ModeloBase y TenantScoped
    }

    protected $fillable = [
        'nombre',
        'descripcion',
        'codigo',
        'centro_id',
        'created_by',
        'updated_by', 
        'deleted_by',
    ];

    // Relaciones específicas del modelo
    public function centro()
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }
}
