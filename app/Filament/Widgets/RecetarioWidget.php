<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use App\Models\Recetario;
use Illuminate\Support\Facades\Auth;

class RecetarioWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.recetario-widget';
    
    protected int | string | array $columnSpan = 'full';
    
    public ?array $data = [];

    public function mount(): void
    {
        $this->loadRecetarioData();
    }

    protected function loadRecetarioData(): void
    {
        $user = Auth::user();
        
        if ($user->medico) {
            $recetario = $user->medico->recetarios()->latest()->first();
            $this->data = [
                'tiene_recetario' => $recetario ? true : false,
            ];
        } else {
            $this->data = [
                'tiene_recetario' => false,
            ];
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Configuración Rápida del Recetario')
                    ->description('Active o desactive su recetario médico')
                    ->schema([
                        Toggle::make('tiene_recetario')
                            ->label('Recetario Activo')
                            ->helperText('Active para habilitar la prescripción de medicamentos')
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->actualizarRecetario($state);
                            }),
                    ])
                    ->headerActions([
                        \Filament\Actions\Action::make('ver_perfil')
                            ->label('Ver Perfil Completo')
                            ->icon('heroicon-o-user-circle')
                            ->url(fn () => route('filament.admin.pages.perfil-medico'))
                            ->openUrlInNewTab(false),
                    ]),
            ])
            ->statePath('data');
    }

    protected function actualizarRecetario(bool $estado): void
    {
        $user = Auth::user();
        
        if (!$user->hasRole('medico')) {
            Notification::make()
                ->title('Error')
                ->body('Solo los médicos pueden activar recetarios.')
                ->danger()
                ->send();
            return;
        }

        if (!$user->medico) {
            Notification::make()
                ->title('Registro Incompleto')
                ->body('Necesita completar su registro de médico para activar el recetario.')
                ->warning()
                ->send();
            return;
        }

        $medico = $user->medico;

        if (!$estado) {
            $medico->recetarios()->delete();
            
            Notification::make()
                ->title('Recetario Desactivado')
                ->success()
                ->send();
        } else {
            $recetario = $medico->recetarios()->latest()->first();
            
            if (!$recetario) {
                Recetario::create([
                    'medico_id' => $medico->id,
                    'consulta_id' => null,
                    'centro_id' => session('current_centro_id') ?? $user->centro_id,
                ]);
                
                Notification::make()
                    ->title('Recetario Activado')
                    ->success()
                    ->send();
            }
        }
        
        $this->loadRecetarioData();
    }

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('medico') ?? false;
    }
}
