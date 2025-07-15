<?php

namespace App\Filament\Resources\Admin\UserResource\Pages;

use App\Filament\Resources\Admin\UserResource;
use App\Models\Persona;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        // Primero crear la persona
        $personaData = $data['persona'] ?? [];
        
        // Asignar centro_id si el usuario no es root
        if (!auth()->user()->hasRole('root')) {
            $personaData['centro_id'] = auth()->user()->centro_id;
        } else {
            $personaData['centro_id'] = $data['centro_id'] ?? auth()->user()->centro_id;
        }
        
        // Asignar created_by
        $personaData['created_by'] = auth()->id();
        
        $persona = Persona::create($personaData);
        
        // Preparar datos del usuario
        $userData = collect($data)->except('persona')->toArray();
        $userData['persona_id'] = $persona->id;
        $userData['created_by'] = auth()->id();
        
        // Asignar centro_id al usuario también
        if (!auth()->user()->hasRole('root')) {
            $userData['centro_id'] = auth()->user()->centro_id;
        }
        
        // Crear el usuario
        $user = User::create($userData);
        
        // Asignar roles si existen
        if (isset($data['roles']) && !empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }
        
        return $user;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Asignar centro_id por defecto si no es root
        if (!auth()->user()->hasRole('root') && !isset($data['centro_id'])) {
            $data['centro_id'] = auth()->user()->centro_id;
        }
        
        return $data;
    }
}
