<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Spatie\Multitenancy\Models\Tenant;
use App\Filament\Widgets\CentroStatsWidget;
use App\Filament\Widgets\CalendarioCitasWidget;
use App\Filament\Widgets\CitasPieChart;
use App\Filament\Widgets\RecetarioWidget;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.dashboard';

    public function getHeading(): string
    {
        $centro = \Spatie\Multitenancy\Models\Tenant::current()?->centro?->nombre_centro ?? 'Sin centro asignado';
        $user = auth()->user();
        $hora = now()->format('H');
        
        $saludo = match(true) {
            $hora < 12 => '🌅 Buenos días',
            $hora < 18 => '☀️ Buenas tardes',
            default => '🌙 Buenas noches'
        };
        
        return $saludo . ', ' . ($user->name ?? 'Usuario');
    }

    public function getSubheading(): ?string
    {
        $centro = \Spatie\Multitenancy\Models\Tenant::current()?->centro?->nombre_centro ?? 'Sin centro asignado';
        return '🏥 ' . $centro . ' • ' . now()->format('l, d \\d\\e F \\d\\e Y');
    }

    public function getWidgets(): array
    {
        return [
            CentroStatsWidget::class,
            CalendarioCitasWidget::class,
            CitasPieChart::class,
            RecetarioWidget::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
            'xl' => 4,
        ];
    }
}