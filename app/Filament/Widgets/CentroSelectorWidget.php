<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Centros_Medico;

class CentroSelectorWidget extends Widget
{
    protected static string $view = 'filament.widgets.centro-selector-widget';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = -1;

    public static function canView(): bool
    {
        return auth()->check();
    }

    public function getCentros()
    {
        $user = auth()->user();
        
        if ($user->hasRole('root')) {
            return Centros_Medico::all();
        }
        
        return $user->getAccessibleCentros();
    }

    public function getCurrentCentro()
    {
        $currentCentroId = session('current_centro_id');
        return $currentCentroId ? Centros_Medico::find($currentCentroId) : null;
    }

    public function switchCentro($centroId)
    {
        $user = auth()->user();
        
        if ($user->canAccessCentro($centroId)) {
            session(['current_centro_id' => $centroId]);
            $this->dispatch('centro-changed');
            
            return redirect()->to(request()->header('Referer') ?: '/admin');
        }
    }
}
