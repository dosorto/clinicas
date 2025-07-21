<?php

namespace App\Filament\Resources\Consultas\ConsultasResource\Pages;

use App\Filament\Resources\Consultas\ConsultasResource;
use App\Models\Pacientes;
use App\Models\Medico;
use App\Models\Consulta;
use App\Models\Citas;
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
    }

    public function getTitle(): string|Htmlable
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
                                    ->whereHas('citas') // Solo pacientes que tienen citas
                                    ->get()
                                    ->filter(function ($p) {
                                        return $p->persona !== null;
                                    })
                                    ->mapWithKeys(function ($p) {
                                        return [$p->id => $p->persona->nombre_completo];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Escriba el nombre del paciente...')
                            ->helperText('Busque y seleccione el paciente que tiene citas programadas.')
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

                        Forms\Components\Select::make('cita_id')
                            ->label('Cita')
                            ->options(function () {
                                if ($this->selectedPatient) {
                                    // Filtrar citas por el paciente seleccionado
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
                                } else {
                                    // Mostrar todas las citas disponibles cuando no hay paciente seleccionado
                                    return Citas::with(['paciente.persona', 'medico.persona'])
                                        ->orderBy('fecha', 'desc')
                                        ->orderBy('hora', 'desc')
                                        ->get()
                                        ->mapWithKeys(function ($cita) {
                                            $pacienteNombre = $cita->paciente && $cita->paciente->persona
                                                ? $cita->paciente->persona->nombre_completo
                                                : 'Sin paciente';

                                            $medicoNombre = $cita->medico && $cita->medico->persona
                                                ? $cita->medico->persona->nombre_completo
                                                : 'Sin médico';

                                            $fechaFormateada = Carbon::parse($cita->fecha)->format('d/m/Y');
                                            $horaFormateada = Carbon::parse($cita->hora)->format('H:i');

                                            return [$cita->id => "Cita #{$cita->id} | {$fechaFormateada} {$horaFormateada} | {$pacienteNombre} - Dr. {$medicoNombre}"];
                                        })
                                        ->toArray();
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder($this->selectedPatient
                                ? 'Seleccionar cita del paciente'
                                : 'Seleccionar cualquier cita disponible')
                            ->helperText($this->selectedPatient
                                ? 'Se muestran solo las citas de ' . $this->selectedPatient->persona->nombre_completo
                                : 'Se muestran todas las citas. Seleccione una para continuar.')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state && !$this->selectedPatient) {
                                    // Si se selecciona una cita sin tener paciente seleccionado,
                                    // obtener el paciente de la cita
                                    $cita = Citas::with('paciente.persona')->find($state);
                                    if ($cita && $cita->paciente) {
                                        $this->selectedPatient = $cita->paciente;
                                        $set('paciente_id', $cita->paciente->id);
                                        $set('medico_id', $cita->medico_id);

                                        Notification::make()
                                            ->title('Paciente y Médico autocargados')
                                            ->body('Se ha seleccionado automáticamente el paciente y médico de la cita.')
                                            ->success()
                                            ->send();
                                    }
                                }
                            })
                            ->columnSpan(2),

                        Forms\Components\Select::make('medico_id')
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
                            ->helperText('Este campo se autocompleta al seleccionar una cita')
                            ->columnSpan(2),
                    ])
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

        Notification::make()
            ->title('Paciente seleccionado')
            ->body('Ahora puede proceder a crear la consulta para ' . $this->selectedPatient->persona->nombre_completo . '. Las citas se han filtrado para este paciente.')
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

        try {
            $consulta = Consulta::create($data);

            Notification::make()
                ->title('Consulta creada exitosamente')
                ->body('La consulta para ' . $this->selectedPatient->persona->nombre_completo . ' ha sido creada.')
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
