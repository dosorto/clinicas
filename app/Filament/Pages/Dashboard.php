<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Spatie\Multitenancy\Models\Tenant;

class Dashboard extends BaseDashboard
{
    public function getHeading(): string
    {
    $centro = \Spatie\Multitenancy\Models\Tenant::current()?->centro?->nombre_centro ?? 'Sin centro asignado';
    return 'Centro actual: ' . $centro;
    }
}
