<?php

namespace App\Policies;
use App\Models\Consulta;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ConsultaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('root')) return true;
        return $user->can('ver consultas');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Consulta $consulta): bool
    {
        if ($user->hasRole('root')) return true;
        return $user->can('ver consultas');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('root')) return true;
        return $user->can('crear consultas');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Consulta $consulta): bool
    {
        if ($user->hasRole('root')) return true;
        return $user->can('actualizar consultas');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Consulta $consulta): bool
    {
        if ($user->hasRole('root')) return true;
        return $user->can('borrar consultas');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Consulta $consulta): bool
    {
        if ($user->hasRole('root')) return true;
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Consulta $consulta): bool
    {
        if ($user->hasRole('root')) return true;
        return false;
    }
}
