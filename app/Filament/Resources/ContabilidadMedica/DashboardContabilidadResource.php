<?php

namespace App\Filament\Resources\ContabilidadMedica;

use App\Filament\Resources\ContabilidadMedica\DashboardContabilidadResource\Pages;
use App\Models\ContabilidadMedica\CargoMedico;
use App\Models\ContabilidadMedica\LiquidacionHonorario;
use App\Models\ContabilidadMedica\PagoCargoMedico;
use App\Models\ContabilidadMedica\PagoHonorario;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Database\Eloquent\Builder;

class DashboardContabilidadResource extends Resource
{
    protected static ?string $model = CargoMedico::class; // Usamos CargoMedico como modelo base, pero es solo referencial

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationGroup = 'Contabilidad Médica';
    protected static ?int $navigationSort = 0; // Para que aparezca primero en el grupo
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $modelLabel = 'Dashboard Contabilidad';
    protected static ?string $pluralModelLabel = 'Dashboard Contabilidad';
    protected static ?string $slug = 'contabilidad-dashboard';
    protected static bool $shouldRegisterNavigation = false; // Ocultar - muy complejo

    public static function getWidgets(): array
    {
        return [
            DashboardContabilidadResource\Widgets\CargosEstadisticasWidget::class,
            DashboardContabilidadResource\Widgets\LiquidacionesEstadisticasWidget::class,
            DashboardContabilidadResource\Widgets\PagosRecientesWidget::class,
            DashboardContabilidadResource\Widgets\GraficoIngresosMensualesWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDashboardContabilidad::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Deshabilitamos la creación porque esto es solo un dashboard
    }
}
