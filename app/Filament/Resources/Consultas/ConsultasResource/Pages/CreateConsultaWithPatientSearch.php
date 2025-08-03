<?php

namespace App\Filament\Resources\Consultas\ConsultasResource\Pages;

use App\Filament\Resources\Consultas\ConsultasResource;
use App\Models\Pacientes;
use App\Models\Medico;
use App\Models\Consulta;
use App\Models\Receta;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CreateConsultaWithPatientSearch extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ConsultasResource::class;

    protected static string $view = 'filament.resources.consultas.pages.create-consulta-with-patient-search';

    public ?array $patientSearchData = [];
    public ?array $consultaData = [];
    public bool $showConsultaForm = false;
    public ?Pacientes $selectedPatient = null;

    public function mount(): void
    {
        $this->patientSearchForm->fill();
        $this->consultaForm->fill();

        // Si se pasa un paciente_id en la URL, precargarlo automáticamente
        if (request()->has('paciente_id')) {
            $pacienteId = request()->get('paciente_id');
            $paciente = Pacientes::with('persona')->find($pacienteId);

            if ($paciente && $paciente->persona) {
                $this->selectedPatient = $paciente;
                $this->showConsultaForm = true;

                // Prellenar los formularios
                $this->patientSearchForm->fill(['paciente_id' => $pacienteId]);
                $this->consultaForm->fill([
                    'paciente_id' => $pacienteId,
                    'centro_id' => Auth::check() ? Auth::user()->centro_id : null,
                ]);

                // Verificar que el paciente fue encontrado
                $message = "Paciente precargado: {$paciente->persona->nombre_completo}.";

                // Mostrar notificación de paciente precargado
                Notification::make()
                    ->title('Paciente precargado')
                    ->body($message)
                    ->success()
                    ->send();
            }
        }
    }    public function getTitle(): string|Htmlable
    {
        return 'Crear Nueva Consulta';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Volver al listado')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }

    public function patientSearchForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Buscar Paciente')
                    ->description('Seleccione el paciente para quien desea crear la consulta')
                    ->schema([
                        Forms\Components\Select::make('paciente_id')
                            ->label('Buscar Paciente')
                            ->options(function () {
                                return Pacientes::with('persona')
                                    ->get()
                                    ->filter(function ($p) {
                                        return $p->persona !== null;
                                    })
                                    ->mapWithKeys(function ($p) {
                                        return [$p->id => $p->persona->nombre_completo . ' - DNI: ' . $p->persona->dni];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Escriba el nombre del paciente...')
                            ->helperText('Busque y seleccione el paciente.')
                            ->columnSpanFull(),
                    ])
                    ->columnSpan('full'),
            ])
            ->statePath('patientSearchData');
    }

    public function consultaForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Paciente Seleccionado')
                    ->schema([
                        Forms\Components\Placeholder::make('patient_info')
                            ->label('')
                            ->content(function () {
                                if ($this->selectedPatient && $this->selectedPatient->persona) {
                                    return view('filament.components.patient-info', [
                                        'patient' => $this->selectedPatient
                                    ]);
                                }
                                return 'No hay paciente seleccionado';
                            })
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Hidden::make('centro_id')
                    ->default(fn () => Auth::check() ? Auth::user()->centro_id : null),

                Forms\Components\Section::make('Información de la Consulta')
                    ->schema([
                        Forms\Components\Hidden::make('paciente_id')
                            ->default(fn () => $this->selectedPatient?->id),

                        Forms\Components\Placeholder::make('medico_info')
                            ->label('Médico')
                            ->content(function () {
                                $user = Auth::user();

                                // Primero intentar con la relación directa
                                if ($user && $user->medico && $user->medico->persona) {
                                    $nombre = $user->medico->persona->nombre_completo;
                                    $dni = $user->medico->persona->dni ?? 'Sin DNI';
                                    return "{$nombre} - DNI: {$dni}";
                                }

                                // Si no tiene relación directa, buscar por persona_id
                                if ($user && $user->persona_id) {
                                    $medico = Medico::withoutGlobalScopes()->where('persona_id', $user->persona_id)->with('persona')->first();
                                    if ($medico && $medico->persona) {
                                        $nombre = $medico->persona->nombre_completo;
                                        $dni = $medico->persona->dni ?? 'Sin DNI';
                                        return "{$nombre} - DNI: {$dni}";
                                    }
                                }

                                // Si tiene persona pero no es médico, mostrar la información del usuario
                                if ($user && $user->persona) {
                                    $nombre = $user->persona->nombre_completo;
                                    $dni = $user->persona->dni ?? 'Sin DNI';
                                    return "{$nombre} - DNI: {$dni} (Usuario)";
                                }

                                return 'No hay médico asociado al usuario';
                            }),

                        Forms\Components\Hidden::make('medico_id')
                            ->default(function () {
                                $user = Auth::user();

                                // Primero intentar con la relación directa
                                if ($user && $user->medico) {
                                    return $user->medico->id;
                                }

                                // Si no tiene relación directa, buscar por persona_id
                                if ($user && $user->persona_id) {
                                    $medico = Medico::withoutGlobalScopes()->where('persona_id', $user->persona_id)->first();
                                    if ($medico) {
                                        return $medico->id;
                                    }
                                }

                                return null;
                            }),
                    ]),

                Forms\Components\Section::make('Detalles Médicos')
                    ->schema([
                        Forms\Components\Textarea::make('diagnostico')
                            ->label('Diagnóstico')
                            ->required()
                            ->rows(4)
                            ->placeholder('Describa el diagnóstico del paciente...')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('tratamiento')
                            ->label('Tratamiento')
                            ->required()
                            ->rows(4)
                            ->placeholder('Describa el tratamiento prescrito...')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->required()
                            ->rows(3)
                            ->placeholder('Describa las observaciones de la consulta...')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Recetas Médicas')
                    ->description('Crear una o varias recetas para el paciente (opcional)')
                    ->schema([
                        Forms\Components\Repeater::make('recetas')
                            ->label('')
                            ->schema([
                                Forms\Components\Textarea::make('medicamentos')
                                    ->label('Medicamentos')
                                    ->required()
                                    ->rows(4)
                                    ->placeholder('Ej: Loratadina 500 mg')
                                    ->helperText('Liste todos los medicamentos con sus dosis y frecuencia')
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('indicaciones')
                                    ->label('Indicaciones')
                                    ->required()
                                    ->rows(3)
                                    ->placeholder('Tomar una diaria, etc.')
                                    ->helperText('Proporcione instrucciones específicas para el paciente')
                                    ->columnSpanFull(),
                            ])
                            ->addActionLabel('Agregar Nueva Receta')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->cloneable()
                            ->deleteAction(
                                fn ($action) => $action->requiresConfirmation()
                            )
                            ->itemLabel(function (array $state): ?string {
                                if (empty($state['medicamentos'])) {
                                    return 'Nueva Receta';
                                }

                                $medicamentos = substr($state['medicamentos'], 0, 50);
                                if (strlen($state['medicamentos']) > 50) {
                                    $medicamentos .= '...';
                                }

                                return 'Receta: ' . $medicamentos;
                            })
                            ->columnSpanFull()
                            ->defaultItems(0)
                            ->hint('Las recetas se crearán automáticamente al guardar la consulta'),
                    ])
                    ->collapsible()
                    ->collapsed(false),
            ])
            ->statePath('consultaData');
    }

    public function selectPatient(): void
    {
        $data = $this->patientSearchForm->getState();

        if (!$data['paciente_id']) {
            Notification::make()
                ->title('Error')
                ->body('Debe seleccionar un paciente.')
                ->danger()
                ->send();
            return;
        }

        $this->selectedPatient = Pacientes::with('persona')->find($data['paciente_id']);

        if (!$this->selectedPatient) {
            Notification::make()
                ->title('Error')
                ->body('Paciente no encontrado.')
                ->danger()
                ->send();
            return;
        }

        $this->showConsultaForm = true;

        // Prellenar el paciente_id en el formulario de consulta
        $this->consultaForm->fill([
            'paciente_id' => $this->selectedPatient->id,
            'centro_id' => Auth::check() ? Auth::user()->centro_id : null,
        ]);

        // Forzar actualización del formulario para que se refresquen las opciones
        $this->dispatch('refreshForm');

        // Verificar información del médico para debugging
        $user = Auth::user();
        $medicoInfo = 'Sin médico';

        if ($user && $user->medico) {
            $medicoInfo = "Médico ID: {$user->medico->id}";
        } elseif ($user && $user->persona_id) {
            $medico = Medico::withoutGlobalScopes()->where('persona_id', $user->persona_id)->first();
            if ($medico) {
                $medicoInfo = "Médico encontrado por persona_id: {$medico->id}";
            }
        }

        $message = "Ahora puede proceder a crear la consulta para {$this->selectedPatient->persona->nombre_completo}. ({$medicoInfo})";

        Notification::make()
            ->title('Paciente seleccionado')
            ->body($message)
            ->success()
            ->send();
    }

    public function changePatient(): void
    {
        $this->showConsultaForm = false;
        $this->selectedPatient = null;
        $this->patientSearchForm->fill();
        $this->consultaForm->fill();
    }

    public function create(): void
    {
        $data = $this->consultaForm->getState();

        // Asegurar que el paciente_id esté presente
        $data['paciente_id'] = $this->selectedPatient->id;

        // Agregar centro_id si está disponible en el usuario autenticado
        if (Auth::check() && Auth::user()->centro_id) {
            $data['centro_id'] = Auth::user()->centro_id;
        }

        // Verificar y obtener medico_id si está vacío
        if (empty($data['medico_id'])) {
            $user = Auth::user();

            // Intentar obtener médico por relación directa
            if ($user && $user->medico) {
                $data['medico_id'] = $user->medico->id;
            }
            // Si no, buscar por persona_id
            elseif ($user && $user->persona_id) {
                $medico = Medico::withoutGlobalScopes()->where('persona_id', $user->persona_id)->first();
                if ($medico) {
                    $data['medico_id'] = $medico->id;
                }
            }
        }

        // Validar que tenemos un medico_id válido
        if (empty($data['medico_id'])) {
            Notification::make()
                ->title('Error: No se pudo determinar el médico')
                ->body('No se encontró un médico asociado al usuario actual. Contacte al administrador.')
                ->danger()
                ->send();
            return;
        }

        // Extraer las recetas del data para procesarlas por separado
        $recetas = $data['recetas'] ?? [];
        unset($data['recetas']); // Remover recetas del data de consulta

        try {
            // Crear la consulta
            $consulta = Consulta::create($data);

            $recetasCreadas = 0;

            // Crear las recetas si existen
            if (!empty($recetas)) {
                foreach ($recetas as $recetaData) {
                    if (!empty($recetaData['medicamentos']) && !empty($recetaData['indicaciones'])) {
                        Receta::create([
                            'medicamentos' => $recetaData['medicamentos'],
                            'indicaciones' => $recetaData['indicaciones'],
                            'paciente_id' => $this->selectedPatient->id,
                            'consulta_id' => $consulta->id,
                            'medico_id' => $data['medico_id'],
                            'centro_id' => $data['centro_id'] ?? null,
                        ]);
                        $recetasCreadas++;
                    }
                }
            }

            $message = 'La consulta para ' . $this->selectedPatient->persona->nombre_completo . ' ha sido creada.';
            if ($recetasCreadas > 0) {
                $message .= " Se crearon {$recetasCreadas} receta(s) médica(s).";
            }

            Notification::make()
                ->title('Consulta creada exitosamente')
                ->body($message)
                ->success()
                ->send();

            // Verificar si hay una cita pendiente desde la sesión
            if (request()->has('cita_id') || session()->has('cita_en_consulta')) {
                $citaId = request()->get('cita_id') ?? session('cita_en_consulta');

                if ($citaId) {
                    $cita = \App\Models\Citas::find($citaId);

                    if ($cita) {
                        // Actualizar el estado de la cita a "Realizado" después de crear la consulta
                        // Utilizamos fill para asegurarnos de que el formato sea correcto
                        $cita->fill(['estado' => 'Realizado']);
                        $cita->save();

                        // Crear notificación adicional
                        Notification::make()
                            ->title('Cita completada')
                            ->body('La cita ha sido marcada como realizado')
                            ->success()
                            ->send();
                    }

                    // Limpiar la sesión
                    session()->forget('cita_en_consulta');
                }
            }

            // Redirigir a la vista previa de la consulta recién creada
            $this->redirect($this->getResource()::getUrl('view', ['record' => $consulta->id]));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al crear la consulta')
                ->body('Ocurrió un error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getForms(): array
    {
        return [
            'patientSearchForm',
            'consultaForm',
        ];
    }
}
