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
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('tratamiento')
                            ->label('Tratamiento')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

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

                Tables\Columns\TextColumn::make('diagnostico')
                    ->label('Diagnóstico')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('tratamiento')
                    ->label('Tratamiento')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Consulta')
                    ->dateTime()
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
                                Infolists\Components\TextEntry::make('id')
                                    ->label('ID'),

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

                Infolists\Components\Section::make('Detalles Médicos')
                    ->schema([
                        Infolists\Components\TextEntry::make('diagnostico')
                            ->label('Diagnóstico')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('tratamiento')
                            ->label('Tratamiento')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('observaciones')
                            ->label('Observaciones')
                            ->columnSpanFull()
                            ->placeholder('Sin observaciones'),
                    ]),

                Infolists\Components\Section::make('Información de Sistema')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Última Actualización')
                                    ->dateTime(),

                                Infolists\Components\TextEntry::make('deleted_at')
                                    ->label('Fecha de Eliminación')
                                    ->dateTime()
                                    ->placeholder('No eliminado'),
                            ]),
                    ])
                    ->collapsible(),
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
            'create' => Pages\CreateConsultas::route('/create'),
            'edit' => Pages\EditConsultas::route('/{record}/edit'),
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
        return static::getModel()::count();
    }
}
