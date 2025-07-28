<?php

namespace App\Filament\Resources\Consultas;

use App\Filament\Resources\Consultas\ConsultasResource\Pages;
use App\Models\Consulta;
use App\Models\Pacientes;
use App\Models\Medico;
use App\Models\Citas;
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
use App\Filament\Resources\Consultas\ConsultasResource\Pages\EditConsultas;
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
                
                Forms\Components\Section::make('Información de la Consulta')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('paciente_id')
                                    ->label('Paciente')
                                    ->options(function () {
                                        return ['' => 'Seleccionar'] + Pacientes::with('persona')->get()->filter(function ($p) {
                                            return $p->persona !== null;
                                        })->mapWithKeys(function ($p) {
                                            return [$p->id => $p->persona->nombre_completo];
                                        })->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(1),

                                Forms\Components\Select::make('medico_id')
                                    ->label('Médico')
                                    ->options(function () {
                                        return ['' => 'Seleccionar'] + Medico::with('persona')->get()->filter(function ($m) {
                                            return $m->persona !== null;
                                        })->mapWithKeys(function ($m) {
                                            return [$m->id => $m->persona->nombre_completo];
                                        })->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Select::make('cita_id')
                            ->label('Cita')
                            ->options(Citas::with(['paciente', 'medico'])
                                ->get()
                                ->mapWithKeys(function ($cita) {
                                    return [$cita->id => "Cita #{$cita->id} - {$cita->fecha} {$cita->hora}"];
                                }))
                            ->searchable()
                            ->preload()
                            ->required(),
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('paciente.persona.nombre_completo')
                    ->label('Paciente')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('medico.persona.nombre_completo')
                    ->label('Médico')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('cita.fecha')
                    ->label('Fecha Cita')
                    ->date()
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
                    ->options(Pacientes::with('persona')->get()->filter(fn($p) => $p->persona !== null)->mapWithKeys(fn($p) => [$p->id => $p->persona->nombre_completo]))
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('medico_id')
                    ->label('Médico')
                    ->options(Medico::with('persona')->get()->filter(fn($m) => $m->persona !== null)->mapWithKeys(fn($m) => [$m->id => $m->persona->nombre_completo]))
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
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('paciente.persona.nombre_completo')
                                    ->label('Paciente'),

                                Infolists\Components\TextEntry::make('medico.persona.nombre_completo')
                                    ->label('Médico'),

                                Infolists\Components\TextEntry::make('cita.fecha')
                                    ->label('Fecha Cita')
                                    ->date(),
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
                    ]),                // Sección de información del sistema ocultada por solicitud del usuario
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
            'edit' => Pages\EditConsultas::route('/{record}/edit'),
            'view' => Pages\ViewConsultas::route('/{record}'),
            'servicios' => Pages\ManageServiciosConsulta::route('/{record}/servicios'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('centro_id', \Filament\Facades\Filament::auth()->user()->centro_id)
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
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
