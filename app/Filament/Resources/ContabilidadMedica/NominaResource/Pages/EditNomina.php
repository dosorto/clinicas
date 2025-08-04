<?php

namespace App\Filament\Resources\ContabilidadMedica\NominaResource\Pages;

use App\Filament\Resources\ContabilidadMedica\NominaResource;
use App\Models\ContabilidadMedica\DetalleNomina;
use App\Models\Medico;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class EditNomina extends EditRecord
{
    protected static string $resource = NominaResource::class;
    
    protected static string $view = 'filament.resources.contabilidad-medica.nomina-resource.pages.create-nomina';

    public $medicosSeleccionados = [];

    public function mount(int | string $record): void
    {
        parent::mount($record);
        $this->loadMedicosFromRecord();
    }

    protected function loadMedicosFromRecord(): void
    {
        $user = Auth::user();
        $centroId = $user ? $user->centro_id : null;
        
        // Usar la relación optimizada para obtener solo médicos con contratos activos
        $query = Medico::with(['persona', 'contratoActivo'])
            ->whereHas('contratosActivos'); // Solo médicos con contratos activos
        
        if ($centroId) {
            $query->where('centro_id', $centroId);
        }
        
        $todosMedicos = $query->get()
            ->filter(function ($medico) {
                // Filtrar solo médicos que tengan persona asociada
                return $medico->persona && $medico->persona->nombre_completo;
            });

        // Obtener los detalles de nómina existentes
        $detallesExistentes = $this->record->detalles()->with('medico.persona')->get();
        
        $this->medicosSeleccionados = $todosMedicos->map(function ($medico) use ($detallesExistentes) {
            $contrato = $medico->contratoActivo;
            $salarioBase = $contrato ? $contrato->salario_mensual : 0;
            
            // Buscar si este médico ya está en la nómina
            $detalleExistente = $detallesExistentes->firstWhere('medico_id', $medico->id);
            
            if ($detalleExistente) {
                return [
                    'id' => $medico->id,
                    'nombre' => $medico->persona->nombre_completo,
                    'salario_base' => $detalleExistente->salario_base,
                    'deducciones' => $detalleExistente->deducciones,
                    'percepciones' => $detalleExistente->percepciones,
                    'total' => $detalleExistente->total_pagar,
                    'seleccionado' => true,
                ];
            } else {
                return [
                    'id' => $medico->id,
                    'nombre' => $medico->persona->nombre_completo,
                    'salario_base' => $salarioBase,
                    'deducciones' => 0,
                    'percepciones' => 0,
                    'total' => $salarioBase,
                    'seleccionado' => false,
                ];
            }
        })->values()->toArray();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información General')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('empresa')
                                    ->label('Centro Médico')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('año')
                                    ->label('Año')
                                    ->required()
                                    ->numeric(),

                                Select::make('mes')
                                    ->label('Mes')
                                    ->options([
                                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                                    ])
                                    ->required(),

                                Select::make('tipo_pago')
                                    ->label('Tipo de Pago')
                                    ->options([
                                        'mensual' => 'Mensual',
                                        'quincenal' => 'Quincenal',
                                        'semanal' => 'Semanal',
                                    ])
                                    ->required(),
                            ]),

                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function toggleSeleccionTodos(): void
    {
        $todosSeleccionados = collect($this->medicosSeleccionados)->every(fn($medico) => $medico['seleccionado']);
        
        foreach ($this->medicosSeleccionados as $index => $medico) {
            $this->medicosSeleccionados[$index]['seleccionado'] = !$todosSeleccionados;
        }
    }

    public function deseleccionarTodos(): void
    {
        foreach ($this->medicosSeleccionados as $index => $medico) {
            $this->medicosSeleccionados[$index]['seleccionado'] = false;
        }
    }

    public function updatedMedicosSeleccionados($value, $key): void
    {
        if (strpos($key, 'deducciones') !== false || strpos($key, 'percepciones') !== false || strpos($key, 'salario_base') !== false) {
            $parts = explode('.', $key);
            $index = $parts[0];
            
            $salario = $this->medicosSeleccionados[$index]['salario_base'] ?? 0;
            $deducciones = $this->medicosSeleccionados[$index]['deducciones'] ?? 0;
            $percepciones = $this->medicosSeleccionados[$index]['percepciones'] ?? 0;
            
            $this->medicosSeleccionados[$index]['total'] = $salario + $percepciones - $deducciones;
        }
    }

    protected function handleRecordUpdate($record, array $data): \App\Models\ContabilidadMedica\Nomina
    {
        // Validar que haya médicos seleccionados
        $medicosSeleccionados = array_filter($this->medicosSeleccionados, fn($medico) => $medico['seleccionado']);
        
        if (empty($medicosSeleccionados)) {
            Notification::make()
                ->title('Error')
                ->body('Debe seleccionar al menos un médico para la nómina.')
                ->danger()
                ->send();
            $this->halt();
        }

        // Actualizar los datos de la nómina
        $record->update($data);

        // Eliminar detalles existentes
        $record->detalles()->delete();

        // Crear nuevos detalles
        foreach ($medicosSeleccionados as $medico) {
            $detalleData = [
                'nomina_id' => $record->id,
                'medico_id' => $medico['id'],
                'medico_nombre' => $medico['nombre'],
                'salario_base' => $medico['salario_base'],
                'deducciones' => $medico['deducciones'],
                'percepciones' => $medico['percepciones'],
                'total_pagar' => $medico['total'],
                'centro_id' => $record->centro_id,
            ];
            
            DetalleNomina::create($detalleData);
        }

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => !$this->record->cerrada),
        ];
    }

    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();
        
        if ($this->record->cerrada) {
            $this->redirect(route('filament.admin.resources.contabilidad-medica.nominas.view', $this->record));
            
            \Filament\Notifications\Notification::make()
                ->title('Nómina cerrada')
                ->body('Esta nómina está cerrada y no puede ser editada.')
                ->warning()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Guardar cambios')
                ->submit('save')
                ->keyBindings(['mod+s'])
                ->color('primary'),
            \Filament\Actions\Action::make('cancel')
                ->label('Cancelar')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }

    protected function getSavedNotification(): ?\Filament\Notifications\Notification
    {
        return Notification::make()
            ->title('Nómina actualizada')
            ->body('La nómina se ha actualizado exitosamente.')
            ->success();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
