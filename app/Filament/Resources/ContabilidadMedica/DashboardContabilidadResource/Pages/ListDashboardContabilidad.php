<?php

namespace App\Filament\Resources\ContabilidadMedica\DashboardContabilidadResource\Pages;

use App\Filament\Resources\ContabilidadMedica\DashboardContabilidadResource;
use Filament\Resources\Pages\Page;
use Filament\Actions;

class ListDashboardContabilidad extends Page
{
    protected static string $resource = DashboardContabilidadResource::class;

    protected static string $view = 'filament.resources.contabilidad-medica.dashboard-contabilidad-resource.pages.list-dashboard-contabilidad';

    protected function getHeaderWidgets(): array
    {
        return DashboardContabilidadResource::getWidgets();
    }
}
