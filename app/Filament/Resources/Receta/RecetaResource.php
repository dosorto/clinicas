<?php

namespace App\Filament\Resources\Receta;

use App\Filament\Resources\Receta\RecetaResource\Pages;
use Filament\Forms\Components\Select;
use App\Models\Receta;
use App\Models\Paciente;
use App\Models\Consulta;
use App\Models\Medico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\InfolistsServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class RecetaResource extends Resource
{
    protected static ?string $model = Receta::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Recetas';

    protected static ?string $modelLabel = 'Receta';

    protected static ?string $pluralModelLabel = 'Recetas';

    protected static ?string $navigationGroup = 'Gestión Médica';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Receta')
                    ->schema([
                        Forms\Components\Select::make('paciente_id')
                            ->label('Paciente')
                            ->options(function () {
                                return ['' => 'Seleccionar'] + \App\Models\Pacientes::with('persona')->get()->filter(function ($p) {
                                    return $p->persona !== null;
                                })->mapWithKeys(function ($p) {
                                    return [$p->id => $p->persona->nombre_completo];
                                })->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('medico_id')
                            ->label('Médico')
                            ->options(function () {
                                return ['' => 'Seleccionar'] + \App\Models\Medico::with('persona')->get()->filter(function ($m) {
                                    return $m->persona !== null;
                                })->mapWithKeys(function ($m) {
                                    return [$m->id => $m->persona->nombre_completo];
                                })->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('consulta_id')
                            ->label('Consulta')
                            ->relationship('consulta', 'id')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->getOptionLabelFromRecordUsing(fn ($record) => "Consulta #{$record->id} - {$record->fecha}"),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detalles de la Receta')
                    ->schema([
                        Forms\Components\Textarea::make('medicamentos')
                            ->label('Medicamentos')
                            ->required()
                            ->rows(4)
                            ->placeholder('Ej: Paracetamol 500mg - 1 tableta cada 8 horas por 5 días')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('indicaciones')
                            ->label('Indicaciones')
                            ->required()
                            ->rows(4)
                            ->placeholder('Instrucciones especiales para el paciente...')
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
                    ->sortable(),

                Tables\Columns\TextColumn::make('paciente.persona.nombre_completo')
                    ->label('Paciente')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('medico.persona.nombre_completo')
                    ->label('Médico')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('consulta_id')
                    ->label('Consulta')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('medicamentos')
                    ->label('Medicamentos')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('indicaciones')
                    ->label('Indicaciones')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                // Eliminar o comentar las columnas de fechas en la tabla
                // Tables\Columns\TextColumn::make('created_at')
                //     ->label('Fecha de Creación')
                //     ->dateTime('d/m/Y H:i')
                //     ->sortable()
                //     ->toggleable(),

                // Tables\Columns\TextColumn::make('updated_at')
                //     ->label('Última Actualización')
                //     ->dateTime('d/m/Y H:i')
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('medico')
                    ->relationship('medico', 'id')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('paciente')
                    ->relationship('paciente', 'id')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Hasta'),
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
                        Infolists\Components\TextEntry::make('paciente.persona.nombre_completo')
                            ->label('Paciente'),
                        Infolists\Components\TextEntry::make('medico.persona.nombre_completo')
                            ->label('Médico'),
                        Infolists\Components\TextEntry::make('consulta_id')
                            ->label('Consulta')
                            ->formatStateUsing(fn ($state) => $state ? "Consulta #{$state}" : 'Sin consulta asociada'),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Detalles de la Receta')
                    ->schema([
                        Infolists\Components\TextEntry::make('medicamentos')
                            ->label('Medicamentos')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('indicaciones')
                            ->label('Indicaciones')
                            ->columnSpanFull(),
                    ]),

                // Eliminar o comentar las entradas de fechas en los infolists
                // Infolists\Components\Section::make('Información del Sistema')
                //     ->schema([
                //         Infolists\Components\TextEntry::make('created_at')
                //             ->label('Fecha de Creación')
                //             ->dateTime('d/m/Y H:i:s'),
                //         Infolists\Components\TextEntry::make('updated_at')
                //             ->label('Última Actualización')
                //             ->dateTime('d/m/Y H:i:s'),
                //     ])
                //     ->columns(2),
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
            'index' => Pages\ListRecetas::route('/'),
            'create' => Pages\CreateReceta::route('/create'),
            'edit' => Pages\EditReceta::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
