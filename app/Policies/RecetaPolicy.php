<?php

namespace App\Policies;
use App\Models\Receta;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RecetaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('root')) return true;
        return $user->can('ver recetas');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Receta $receta): bool
    {
        if ($user->hasRole('root')) return true;
        return $user->can('ver recetas');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('root')) return true;
        return $user->can('crear recetas');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Receta $receta): bool
    {
        if ($user->hasRole('root')) return true;
        return $user->can('actualizar recetas');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Receta $receta): bool
    {
        if ($user->hasRole('root')) return true;
        return $user->can('borrar recetas');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Receta $receta): bool
    {
        if ($user->hasRole('root')) return true;
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Receta $receta): bool
    {
        if ($user->hasRole('root')) return true;
        return false;
    }
}

