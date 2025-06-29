<?php

namespace App\Filament\Resources\Persona\PersonaResource\Pages;

use App\Filament\Resources\Persona\PersonaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Facades\Filament;

class CreatePersona extends CreateRecord
{
    protected static string $resource = PersonaResource::class;

    public static function mutateFormDataUsing(array $data): array
    {
    $data['created_by'] = Filament::auth()->id() ?? auth()->id();
    return $data;
    }
}
