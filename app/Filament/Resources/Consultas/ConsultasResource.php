<?php

namespace App\Filament\Resources\Consultas;

use App\Filament\Resources\Consultas\ConsultasResource\Pages;
use App\Models\Consulta;
use App\Models\Pacientes;
use App\Models\Medico;
use App\Models\Receta;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Consultas\ConsultasResource\Pages\ListConsultas;
use App\Filament\Resources\Consultas\ConsultasResource\Pages\CreateConsultas;
use App\Filament\Resources\Consultas\ConsultasResource\Pages\CreateConsultaWithPatientSearch;
use App\Filament\Resources\Consultas\ConsultasResource\Pages\EditConsultas;
use App\Filament\Resources\Consultas\ConsultasResource\Pages\ViewConsultas;
use App\Filament\Resources\Consultas\ConsultasResource\Pages\ManageServiciosConsulta;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;


class ConsultasResource extends Resource
{
    protected static ?string $model = Consulta::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Consultas';

    protected static ?string $modelLabel = 'Consulta';

    protected static ?string $pluralModelLabel = 'Consultas';

    protected static ?string $navigationGroup = 'Gestión Médica';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // El campo centro_id se asigna automáticamente y se oculta
                Forms\Components\Hidden::make('centro_id')
                    ->default(fn () => \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::user()->centro_id : null),

                // Campo paciente_id oculto - se debe asignar al crear la consulta
                Forms\Components\Hidden::make('paciente_id')
                    ->default(null),

                Forms\Components\Section::make('Información de la Consulta')
                    ->schema([
                        Forms\Components\Placeholder::make('medico_info')
                            ->label('Médico')
                            ->content(function () {
                                $user = \Illuminate\Support\Facades\Auth::user();

                                // Primero intentar con la relación directa
                                if ($user && $user->medico && $user->medico->persona) {
                                    $nombre = $user->medico->persona->nombre_completo;
                                    $dni = $user->medico->persona->dni ?? 'Sin DNI';
                                    return "{$nombre} - DNI: {$dni}";
                                }

                                // Si no tiene relación directa, buscar por persona_id
                                if ($user && $user->persona_id) {
                                    $medico = \App\Models\Medico::withoutGlobalScopes()->where('persona_id', $user->persona_id)->with('persona')->first();
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
                                $user = \Illuminate\Support\Facades\Auth::user();

                                // Primero intentar con la relación directa
                                if ($user && $user->medico) {
                                    return $user->medico->id;
                                }

                                // Si no tiene relación directa, buscar por persona_id
                                if ($user && $user->persona_id) {
                                    $medico = \App\Models\Medico::withoutGlobalScopes()->where('persona_id', $user->persona_id)->first();
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
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->autosize(),

                        Forms\Components\Textarea::make('tratamiento')
                            ->label('Tratamiento')
                            ->required()
                            ->rows(4)
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->autosize(),

                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->autosize(),
                    ]),

                Forms\Components\Section::make('Recetas Médicas')
                    ->schema([
                        Forms\Components\Repeater::make('recetas')
                            ->relationship('recetas')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\DatePicker::make('fecha_receta')
                                            ->label('Fecha de Receta')
                                            ->default(now())
                                            ->required()
                                            ->displayFormat('d/m/Y')
                                            ->native(false),

                                        Forms\Components\Placeholder::make('receta_info')
                                            ->label('Información')
                                            ->content(function ($get) {
                                                $fecha = $get('fecha_receta');
                                                if ($fecha) {
                                                    return 'Receta del ' . \Carbon\Carbon::parse($fecha)->format('d/m/Y');
                                                }
                                                return 'Nueva receta';
                                            })
                                            ->extraAttributes(['class' => 'text-sm text-gray-600']),
                                    ]),

                                Forms\Components\Textarea::make('medicamentos')
                                    ->label('💊 Medicamentos')
                                    ->required()
                                    ->rows(4)
                                    ->maxLength(65535)
                                    ->columnSpanFull()
                                    ->autosize()
                                    ->placeholder('Ej: Paracetamol 500mg - 1 tableta cada 8 horas por 3 días')
                                    ->helperText('Incluya nombre, dosis, frecuencia y duración de cada medicamento'),

                                Forms\Components\Textarea::make('indicaciones')
                                    ->label('📋 Indicaciones')
                                    ->required()
                                    ->rows(4)
                                    ->maxLength(65535)
                                    ->columnSpanFull()
                                    ->autosize()
                                    ->placeholder('Ej: Tomar con alimentos, evitar alcohol, regresar en 7 días')
                                    ->helperText('Instrucciones especiales, precauciones y seguimiento'),
                            ])
                            ->itemLabel(fn (array $state): ?string =>
                                !empty($state['fecha_receta'])
                                    ? 'Receta del ' . \Carbon\Carbon::parse($state['fecha_receta'])->format('d/m/Y')
                                    : 'Nueva receta'
                            )
                            ->collapsible()
                            ->collapsed(false)
                            ->columnSpanFull()
                            ->minItems(0)
                            ->maxItems(10)
                            ->defaultItems(0)
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                $data['centro_id'] = \Illuminate\Support\Facades\Auth::user()->centro_id ?? null;
                                return $data;
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                $data['centro_id'] = \Illuminate\Support\Facades\Auth::user()->centro_id ?? null;
                                return $data;
                            }),
                    ])
                    ->description('Gestione las recetas médicas asociadas a esta consulta')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->collapsible()
                    ->collapsed(false)
                    ->visible(fn (string $operation): bool => $operation === 'edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('paciente_nombre')
                    ->label('Paciente')
                    ->state(function (Consulta $record): string {
                        if ($record->paciente && $record->paciente->persona) {
                            return $record->paciente->persona->nombre_completo;
                        }
                        return 'N/A';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('paciente.persona', function (Builder $subQuery) use ($search) {
                            $subQuery->where('primer_nombre', 'like', "%{$search}%")
                                     ->orWhere('segundo_nombre', 'like', "%{$search}%")
                                     ->orWhere('primer_apellido', 'like', "%{$search}%")
                                     ->orWhere('segundo_apellido', 'like', "%{$search}%")
                                     ->orWhere('dni', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('medico_nombre')
                    ->label('Médico')
                    ->state(function (Consulta $record): string {
                        if ($record->medico && $record->medico->persona) {
                            return $record->medico->persona->nombre_completo;
                        }

                        // Si no se cargó la relación, intentar cargarla manualmente
                        if ($record->medico_id) {
                            $medico = Medico::withoutGlobalScopes()->with('persona')->find($record->medico_id);
                            if ($medico && $medico->persona) {
                                return $medico->persona->nombre_completo;
                            }
                        }

                        return 'N/A';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('medico.persona', function (Builder $subQuery) use ($search) {
                            $subQuery->where('primer_nombre', 'like', "%{$search}%")
                                     ->orWhere('segundo_nombre', 'like', "%{$search}%")
                                     ->orWhere('primer_apellido', 'like', "%{$search}%")
                                     ->orWhere('segundo_apellido', 'like', "%{$search}%")
                                     ->orWhere('dni', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Eliminada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('paciente_id')
                    ->label('Paciente')
                    ->options(function () {
                        return Pacientes::with('persona')
                            ->get()
                            ->filter(fn($p) => $p->persona !== null)
                            ->mapWithKeys(function ($p) {
                                $nombre = $p->persona->primer_nombre . ' ' .
                                         ($p->persona->segundo_nombre ? $p->persona->segundo_nombre . ' ' : '') .
                                         $p->persona->primer_apellido . ' ' .
                                         ($p->persona->segundo_apellido ? $p->persona->segundo_apellido : '');
                                return [$p->id => trim($nombre)];
                            });
                    })
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('medico_id')
                    ->label('Médico')
                    ->options(function () {
                        return Medico::with('persona')
                            ->get()
                            ->filter(fn($m) => $m->persona !== null)
                            ->mapWithKeys(function ($m) {
                                $nombre = $m->persona->primer_nombre . ' ' .
                                         ($m->persona->segundo_nombre ? $m->persona->segundo_nombre . ' ' : '') .
                                         $m->persona->primer_apellido . ' ' .
                                         ($m->persona->segundo_apellido ? $m->persona->segundo_apellido : '');
                                return [$m->id => trim($nombre)];
                            });
                    })
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Fecha desde'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Fecha hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Información General')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Fecha de Consulta')
                                    ->dateTime(),
                            ]),
                        ]),


                Infolists\Components\Section::make('Participantes')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('paciente_nombre')
                                    ->label('Paciente')
                                    ->state(function (Consulta $record): string {
                                        if ($record->paciente && $record->paciente->persona) {
                                            return $record->paciente->persona->nombre_completo;
                                        }
                                        return 'No disponible';
                                    }),

                                Infolists\Components\TextEntry::make('medico_nombre')
                                    ->label('Médico')
                                    ->state(function (Consulta $record): string {
                                        // Debug: verificar qué está pasando con el médico
                                        if (!$record->medico) {
                                            // Intentar cargar el médico manualmente
                                            $record->load('medico.persona');
                                        }

                                        if ($record->medico && $record->medico->persona) {
                                            return $record->medico->persona->nombre_completo;
                                        }

                                        // Si todavía no funciona, intentar buscar el médico por ID usando withoutGlobalScopes
                                        if ($record->medico_id) {
                                            $medico = Medico::withoutGlobalScopes()->with('persona')->find($record->medico_id);
                                            if ($medico && $medico->persona) {
                                                return $medico->persona->nombre_completo;
                                            }
                                        }

                                        return 'No disponible (Médico ID: ' . ($record->medico_id ?? 'null') . ')';
                                    }),
                            ]),
                    ]),

                Infolists\Components\Section::make('Detalles de Consulta')
                    ->schema([
                        Infolists\Components\Section::make('Diagnóstico')
                            ->schema([
                                Infolists\Components\TextEntry::make('diagnostico')
                                    ->hiddenLabel()
                                    ->placeholder('Sin diagnóstico registrado')
                                    ->columnSpanFull()
                                    ->formatStateUsing(fn (?string $state): string => $state ?: 'Sin diagnóstico registrado')
                                    ->copyable()
                                    ->extraAttributes([
                                        'style' => 'white-space: pre-line; text-align: left; word-wrap: break-word; max-height: 200px; overflow-y: auto; padding: 12px; border-radius: 6px; border: 1px solid; line-height: 1.6;',
                                        'class' => 'bg-gray-50 border-gray-200 text-gray-900 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100'
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed(false),

                        Infolists\Components\Section::make('Tratamiento')
                            ->schema([
                                Infolists\Components\TextEntry::make('tratamiento')
                                    ->hiddenLabel()
                                    ->placeholder('Sin tratamiento registrado')
                                    ->columnSpanFull()
                                    ->formatStateUsing(fn (?string $state): string => $state ?: 'Sin tratamiento registrado')
                                    ->copyable()
                                    ->extraAttributes([
                                        'style' => 'white-space: pre-line; text-align: left; word-wrap: break-word; max-height: 200px; overflow-y: auto; padding: 12px; border-radius: 6px; border: 1px solid; line-height: 1.6;',
                                        'class' => 'bg-gray-50 border-gray-200 text-gray-900 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100'
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed(false),

                        Infolists\Components\Section::make('Observaciones')
                            ->schema([
                                Infolists\Components\TextEntry::make('observaciones')
                                    ->hiddenLabel()
                                    ->placeholder('Sin observaciones registradas')
                                    ->columnSpanFull()
                                    ->formatStateUsing(fn (?string $state): string => $state ?: 'Sin observaciones registradas')
                                    ->copyable()
                                    ->extraAttributes([
                                        'style' => 'white-space: pre-line; text-align: left; word-wrap: break-word; max-height: 200px; overflow-y: auto; padding: 12px; border-radius: 6px; border: 1px solid; line-height: 1.6;',
                                        'class' => 'bg-gray-50 border-gray-200 text-gray-900 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100'
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed(false),
                    ]),

                Infolists\Components\Section::make('Recetas Médicas')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Tabs::make('Recetas')
                                ->tabs([
                                    Infolists\Components\Tabs\Tab::make('recetas_tab')
                                        ->label('Recetas Médicas')
                                        ->schema([
                                            Infolists\Components\RepeatableEntry::make('recetas')
                                                ->label('')
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('fecha_receta')
                                                        ->label('Fecha')
                                                        ->date(),
                                                    Infolists\Components\TextEntry::make('medicamentos')
                                                        ->label('Medicamentos')
                                                        ->extraAttributes([
                                                            'class' => 'prose max-w-none text-success-600 dark:text-success-400',
                                                        ]),
                                                    Infolists\Components\TextEntry::make('indicaciones')
                                                        ->label('Indicaciones')
                                                        ->extraAttributes([
                                                            'class' => 'prose max-w-none text-info-600 dark:text-info-400',
                                                        ]),
                                                ])
                                                ->columns(3),
                                        ]),
                                ])
                                ->contained(false),
                        ])
                            ->from('md'),
                    ])
                    ->description('Lista de todas las recetas médicas emitidas durante esta consulta')
                    ->collapsible()
                    ->collapsed(false)
                    ->icon('heroicon-o-clipboard-document-list'),

                // Sección de información del sistema ocultada por solicitud del usuario
                // Infolists\Components\Section::make('Información de Sistema')
                //     ->schema([
                //         Infolists\Components\Grid::make(2)
                //             ->schema([
                //                 Infolists\Components\TextEntry::make('updated_at')
                //                     ->label('Última Actualización')
                //                     ->dateTime(),

                //                 Infolists\Components\TextEntry::make('deleted_at')
                //                     ->label('Fecha de Eliminación')
                //                     ->dateTime()
                //                     ->placeholder('No eliminado'),
                //             ]),
                //     ])
                //     ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConsultas::route('/'),
            'create' => Pages\CreateConsultaWithPatientSearch::route('/create'),
            'create-simple' => Pages\CreateConsultas::route('/create-simple'),
            'view' => Pages\ViewConsultas::route('/{record}'),
            'edit' => Pages\EditConsultas::route('/{record}/edit'),
            'manage-servicios' => Pages\ManageServiciosConsulta::route('/{record}/servicios'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['paciente.persona', 'medico.persona', 'recetas'])
            ->where('centro_id', \Filament\Facades\Filament::auth()->user()->centro_id)
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        $user = \Illuminate\Support\Facades\Auth::user();

        // Si el usuario es un médico, solo mostrar sus consultas
        if ($user) {
            // Primero intentar con la relación directa
            if ($user->medico) {
                $query->where('medico_id', $user->medico->id);
            }
            // Si no tiene relación directa, buscar por persona_id
            elseif ($user->persona_id) {
                $medico = \App\Models\Medico::withoutGlobalScopes()
                    ->where('persona_id', $user->persona_id)
                    ->first();
                    
                if ($medico) {
                    $query->where('medico_id', $medico->id);
                }
            }
        }

        return $query;
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            $modelClass = static::getModel();
            if (!$modelClass) {
                return null;
            }
            return (string) $modelClass::count();
        } catch (\Exception $e) {
            return null;
        }
    }


}
