<?php

namespace App\Policies;

use App\Models\Examenes;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExamenesPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        // Todos los usuarios autenticados pueden ver exámenes
        return true;
    }

    public function view(User $user, Examenes $examen)
    {
        // Root puede ver todo
        if ($user->roles->contains('name', 'root')) {
            return true;
        }

        // Administradores pueden ver exámenes de su centro
        if ($user->roles->contains('name', 'administrador')) {
            return $examen->centro_id === session('current_centro_id');
        }

        // Médicos solo pueden ver sus propios exámenes
        if ($user->roles->contains('name', 'medico')) {
            return $user->medico && $examen->medico_id === $user->medico->id;
        }

        return false;
    }

    public function create(User $user)
    {
        // Solo médicos pueden crear exámenes
        return $user->roles->contains('name', 'medico');
    }

    public function update(User $user, Examenes $examen)
    {
        // Root puede editar todo
        if ($user->roles->contains('name', 'root')) {
            return true;
        }

        // Administradores pueden editar exámenes de su centro (para subir imágenes)
        if ($user->roles->contains('name', 'administrador')) {
            return $examen->centro_id === session('current_centro_id');
        }

        // Médicos pueden editar sus propios exámenes
        if ($user->roles->contains('name', 'medico')) {
            return $user->medico && $examen->medico_id === $user->medico->id;
        }

        return false;
    }

    public function delete(User $user, Examenes $examen)
    {
        // Root puede eliminar todo
        if ($user->roles->contains('name', 'root')) {
            return true;
        }

        // Solo médicos pueden eliminar sus propios exámenes (y solo cuando están solicitados)
        if ($user->roles->contains('name', 'medico')) {
            return $user->medico && 
                   $examen->medico_id === $user->medico->id && 
                   $examen->estado === 'Solicitado';
        }

        return false;
    }

    public function uploadResult(User $user, Examenes $examen)
    {
        // Root puede subir resultados
        if ($user->roles->contains('name', 'root')) {
            return true;
        }

        // Administradores pueden subir resultados de su centro
        if ($user->roles->contains('name', 'administrador')) {
            return $examen->centro_id === session('current_centro_id');
        }

        // Médicos pueden subir resultados de sus propios exámenes
        if ($user->roles->contains('name', 'medico')) {
            return $user->medico && $examen->medico_id === $user->medico->id;
        }

        return false;
    }
}
