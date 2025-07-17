<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;


class User extends Authenticatable
{
    
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }
    public function centro()
    {
        return $this->belongsTo(Centros_Medico::class, 'centro_id');
    }
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'persona_id',
        'created_by', // ID del usuario que creó el registro
        'updated_by', // ID del usuario que actualizó el registro
        'centro_id', // ID del centro médico, puede ser nulo
        
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted()
    {
        parent::booted();
        
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
        
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
        
        static::deleting(function ($model) {
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->save();
            }
        });
    }

    /**
     * Verifica si el usuario puede acceder a un centro específico
     */
    public function canAccessCentro($centroId): bool
    {
        // Root puede acceder a cualquier centro
        if ($this->hasRole('root')) {
            return true;
        }
        
        // Usuarios normales solo pueden acceder a su centro asignado
        return $this->centro_id == $centroId;
    }

    /**
     * Establece el tenant actual para el usuario
     */
    public function switchToTenant($centroId): bool
    {
        if (!$this->canAccessCentro($centroId)) {
            return false;
        }

        $tenant = \App\Models\Tenant::where('centro_id', $centroId)->first();
        if ($tenant) {
            $tenant->makeCurrent();
            return true;
        }

        return false;
    }

    /**
     * Obtiene todos los centros a los que el usuario puede acceder
     */
    public function getAccessibleCentros()
    {
        if ($this->hasRole('root')) {
            return \App\Models\Centros_Medico::all();
        }
        
        return \App\Models\Centros_Medico::where('id', $this->centro_id)->get();
    }

    /**
     * Obtiene los roles del usuario para un centro específico
     */
    public function getRolesForCentro($centroId)
    {
        // Si es root, tiene todos los permisos
        if ($this->hasRole('root')) {
            return $this->roles;
        }

        // Si no puede acceder al centro, no tiene roles
        if (!$this->canAccessCentro($centroId)) {
            return collect();
        }

        // Retorna los roles del usuario (podrías extender esto para roles específicos por centro)
        return $this->roles;
    }
}
