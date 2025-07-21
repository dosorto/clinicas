<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;
use App\Models\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends SpatieRole
{
    use TenantScoped; // Assuming you have a trait for tenant scoping

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'guard_name',
        'centro_id', // Assuming you want to add centro_id for tenant scoping
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }

    // Agregar la relación con el centro médico
    public function centro(): BelongsTo
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($model) {
            if (!$model->centro_id && session()->has('current_centro_id')) {
                $model->centro_id = session('current_centro_id');
            }
        });

        // Agregar validación para evitar modificación del centro_id
        static::updating(function ($model) {
            if ($model->isDirty('centro_id')) {
                $model->centro_id = $model->getOriginal('centro_id');
            }
        });
    }

}

