<?php

namespace App\Filament\Resources\Consultas\ConsultasResource\Pages;

use App\Filament\Resources\Consultas\ConsultasResource;
use App\Models\Pacientes;
use App\Models\Medico;
use App\Models\Consulta;
use App\Models\Citas;
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

                // Verificar si el paciente tiene citas para el mensaje
                $citasCount = Citas::where('paciente_id', $paciente->id)->count();
                $message = $citasCount > 0
                    ? "Paciente precargado: {$paciente->persona->nombre_completo}. Se encontraron {$citasCount} cita(s) disponible(s)."
                    : "Paciente precargado: {$paciente->persona->nombre_completo}. Este paciente no tiene citas programadas.";

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
                    ->schema(function () {
                        $fields = [
                            Forms\Components\Hidden::make('paciente_id')
                                ->default(fn () => $this->selectedPatient?->id),
                        ];

                        // Solo mostrar el campo de cita si el paciente tiene citas
                        if ($this->selectedPatient) {
                            $citasCount = Citas::where('paciente_id', $this->selectedPatient->id)->count();

                            if ($citasCount > 0) {
                                $fields[] = Forms\Components\Select::make('cita_id')
                                    ->label('Cita')
                                    ->options(function () {
                                        return Citas::where('paciente_id', $this->selectedPatient->id)
                                            ->with(['paciente.persona', 'medico.persona'])
                                            ->orderBy('fecha', 'desc')
                                            ->orderBy('hora', 'desc')
                                            ->get()
                                            ->mapWithKeys(function ($cita) {
                                                $medicoNombre = $cita->medico && $cita->medico->persona
                                                    ? $cita->medico->persona->nombre_completo
                                                    : 'Sin médico asignado';

                                                $fechaFormateada = Carbon::parse($cita->fecha)->format('d/m/Y');
                                                $horaFormateada = Carbon::parse($cita->hora)->format('H:i');

                                                return [$cita->id => "Cita #{$cita->id} | {$fechaFormateada} a las {$horaFormateada} | Dr. {$medicoNombre}"];
                                            })
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->placeholder('Seleccionar cita del paciente (opcional)')
                                    ->helperText('Se muestran las citas de ' . $this->selectedPatient->persona->nombre_completo . '. Puede crear la consulta sin seleccionar una cita.')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $cita = Citas::with('medico')->find($state);
                                            if ($cita && $cita->medico_id) {
                                                $set('medico_id', $cita->medico_id);
                                                Notification::make()
                                                    ->title('Médico autocargado')
                                                    ->body('Se ha seleccionado automáticamente el médico de la cita.')
                                                    ->success()
                                                    ->send();
                                            }
                                        }
                                    })
                                    ->columnSpan(2);
                            } else {
                                $fields[] = Forms\Components\Placeholder::make('no_citas')
                                    ->label('Información')
                                    ->content('Este paciente no tiene citas programadas. Puede crear la consulta seleccionando un médico directamente.')
                                    ->columnSpanFull();
                            }
                        }

                        $fields[] = Forms\Components\Select::make('medico_id')
                            ->label('Médico')
                            ->options(function () {
                                return Medico::with('persona')
                                    ->get()
                                    ->filter(function ($m) {
                                        return $m->persona !== null;
                                    })
                                    ->mapWithKeys(function ($m) {
                                        return [$m->id => $m->persona->nombre_completo];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Seleccione el médico que realizará la consulta')
                            ->columnSpan(2);

                        return $fields;
                    })
                    ->columns(4),

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
                                    ->placeholder('Ej: Paracetamol 500mg - 1 tableta cada 8 horas por 5 días\nAmoxicilina 500mg - 1 cápsula cada 12 horas por 7 días')
                                    ->helperText('Liste todos los medicamentos con sus dosis y frecuencia')
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('indicaciones')
                                    ->label('Indicaciones')
                                    ->required()
                                    ->rows(3)
                                    ->placeholder('Instrucciones especiales para el paciente: tomar con alimentos, evitar alcohol, etc.')
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

        // Forzar actualización del formulario para que se refresquen las opciones de citas
        $this->dispatch('refreshForm');

        // Verificar si el paciente tiene citas
        $citasCount = Citas::where('paciente_id', $this->selectedPatient->id)->count();

        $message = $citasCount > 0
            ? "Ahora puede proceder a crear la consulta para {$this->selectedPatient->persona->nombre_completo}. Se encontraron {$citasCount} cita(s) disponible(s)."
            : "Ahora puede proceder a crear la consulta para {$this->selectedPatient->persona->nombre_completo}. Este paciente no tiene citas programadas.";

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
                            'medico_id' => $data['medico_id'] ?? null,
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

            $this->redirect($this->getResource()::getUrl('index'));
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
