<?php

namespace App\Filament\Resources\ContabilidadMedica;

use App\Filament\Resources\ContabilidadMedica\CargoMedicoResource\Pages;
use App\Filament\Resources\ContabilidadMedica\CargoMedicoResource\RelationManagers;
use App\Models\ContabilidadMedica\CargoMedico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ContabilidadMedica\CargoMedicoResource\RelationManagers\PagosRelationManager;    


class CargoMedicoResource extends Resource
{
    protected static ?string $model = CargoMedico::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Contabilidad Médica';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('medico_id')
                    ->relationship('medico', 'persona_id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->persona->nombre_completo)
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('contrato_id')
                    ->relationship('contrato', 'id')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('centro_id')
                    ->relationship('centro', 'nombre_centro')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('descripcion')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('periodo_inicio')
                    ->required(),
                Forms\Components\DatePicker::make('periodo_fin')
                    ->required(),
                Forms\Components\TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->prefix('L'),
                Forms\Components\TextInput::make('impuesto_total')
                    ->required()
                    ->numeric()
                    ->prefix('L'),
                Forms\Components\TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->prefix('L'),
                Forms\Components\Select::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'parcial' => 'Pago Parcial',
                        'pagado' => 'Pagado',
                        'anulado' => 'Anulado'
                    ])
                    ->required(),
                Forms\Components\Textarea::make('observaciones')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('medico.persona.nombre_completo')
                    ->label('Médico')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('persona', function ($query) use ($search) {
                            $query->where('primer_nombre', 'like', "%{$search}%")
                                  ->orWhere('primer_apellido', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->searchable(),
                Tables\Columns\TextColumn::make('periodo_inicio')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('periodo_fin')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->money('HNL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'parcial' => 'info',
                        'pagado' => 'success',
                        'anulado' => 'danger',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'parcial' => 'Pago Parcial',
                        'pagado' => 'Pagado',
                        'anulado' => 'Anulado'
                    ]),
                Tables\Filters\SelectFilter::make('medico')
                    ->relationship('medico.persona', 'primer_nombre')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->nombre_completo),
                Tables\Filters\Filter::make('periodo_inicio')
                    ->form([
                        Forms\Components\DatePicker::make('desde'),
                        Forms\Components\DatePicker::make('hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('periodo_inicio', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('periodo_inicio', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('periodo_fin')
                    ->form([
                        Forms\Components\DatePicker::make('desde'),
                        Forms\Components\DatePicker::make('hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('periodo_fin', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('periodo_fin', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PagosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCargoMedicos::route('/'),
            'create' => Pages\CreateCargoMedico::route('/create'),
            'view' => Pages\ViewCargoMedico::route('/{record}'),
            'edit' => Pages\EditCargoMedico::route('/{record}/edit'),
        ];
    }
}
