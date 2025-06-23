<?php

namespace App\Filament\Resources\Admin\UserResource\Pages;

use App\Filament\Resources\Admin\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Facades\Filament;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public static function mutateFormDataUsing(array $data): array
    {
    $data['created_by'] = Filament::auth()->id() ?? auth()->id();
    return $data;
    }
}
