<?php

namespace App\Filament\Resources\ContabilidadMedica\NominaResource\Pages;

use App\Filament\Resources\ContabilidadMedica\NominaResource;
use App\Models\ContabilidadMedica\DetalleNomina;
use App\Models\Medico;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Checkbox;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CreateNomina extends CreateRecord
{
    protected static string $resource = NominaResource::class;

    protected static string $view = 'filament.resources.contabilidad-medica.nomina-resource.pages.create-nomina';

    public $medicosSeleccionados = [];

    public function mount(): void
    {
        parent::mount();
        $this->loadMedicos();
    }

    protected function loadMedicos(): void
    {
        $user = Auth::user();
        $centroId = $user ? $user->centro_id : null;
        
        $query = Medico::with(['persona', 'contratos']);
        
        // Filtrar por centro médico del usuario si existe
        if ($centroId) {
            $query->where('centro_id', $centroId);
        }
        
        $this->medicosSeleccionados = $query->get()
            ->filter(function ($medico) {
                // Filtrar solo médicos que tengan persona asociada
                return $medico->persona && $medico->persona->nombre_completo;
            })
            ->map(function ($medico) {
                $contrato = $medico->contratos()->where('activo', true)->first();
                $salario = $contrato ? $contrato->salario_mensual : 0;
                
                return [
                    'id' => $medico->id,
                    'nombre' => $medico->persona->nombre_completo,
                    'salario_base' => $salario,
                    'deducciones' => 0,
                    'percepciones' => 0,
                    'total' => $salario,
                    'seleccionado' => false,
                ];
            })
            ->values() // Reindexar el array
            ->toArray();
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
                                    ->default(function () {
                                        $user = Auth::user();
                                        if ($user && $user->centro) {
                                            return $user->centro->nombre_centro;
                                        }
                                        return 'Centro Médico';
                                    })
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('año')
                                    ->label('Año')
                                    ->required()
                                    ->numeric()
                                    ->default(date('Y')),

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
                                    ->required()
                                    ->default('mensual'),
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
        // Recalcular totales cuando cambian los valores
        if (strpos($key, 'deducciones') !== false || strpos($key, 'percepciones') !== false || strpos($key, 'salario_base') !== false) {
            $parts = explode('.', $key);
            $index = $parts[0];
            
            $salario = $this->medicosSeleccionados[$index]['salario_base'] ?? 0;
            $deducciones = $this->medicosSeleccionados[$index]['deducciones'] ?? 0;
            $percepciones = $this->medicosSeleccionados[$index]['percepciones'] ?? 0;
            
            $this->medicosSeleccionados[$index]['total'] = $salario + $percepciones - $deducciones;
        }
    }

    public function create(bool $another = false): void
    {
        $data = $this->form->getState();

        // Validar que haya médicos seleccionados
        $medicosSeleccionados = array_filter($this->medicosSeleccionados, fn($medico) => $medico['seleccionado']);
        
        if (empty($medicosSeleccionados)) {
            Notification::make()
                ->title('Error')
                ->body('Debe seleccionar al menos un médico para crear la nómina.')
                ->danger()
                ->send();
            return;
        }

        // Crear la nómina
        $user = Auth::user();
        $data['centro_id'] = $user ? $user->centro_id : null;
        $nomina = $this->getModel()::create($data);

        // Crear los detalles de nómina
        foreach ($medicosSeleccionados as $medico) {
            $data = [
                'nomina_id' => $nomina->id,
                'medico_id' => $medico['id'],
                'medico_nombre' => $medico['nombre'],
                'salario_base' => $medico['salario_base'],
                'deducciones' => $medico['deducciones'],
                'percepciones' => $medico['percepciones'],
                'total_pagar' => $medico['total'],
                'centro_id' => $nomina->centro_id,
            ];
            
            // Asegurar que no haya campos problemáticos
            unset($data['created_by'], $data['updated_by'], $data['deleted_by']);
            
            DetalleNomina::create($data);
        }

        Notification::make()
            ->title('Nómina creada')
            ->body('La nómina se ha creado exitosamente.')
            ->success()
            ->send();

        $this->redirect($this->getRedirectUrl());
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
