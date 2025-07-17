<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class CentroSelectorWidget extends Widget
{
    protected static string $view = 'filament.widgets.centro-selector-widget';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = -1; // Para que aparezca primero

    public static function canView(): bool
    {
        return auth()->check();
    }
}
