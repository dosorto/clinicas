<?php

namespace App\Filament\Resources\Impuestos;

use App\Filament\Resources\Impuestos\ImpuestosResource\Pages;
use App\Filament\Resources\Impuestos\ImpuestosResource\RelationManagers;
use App\Models\Impuesto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ImpuestosResource extends Resource
{
    protected static ?string $model = Impuesto::class;

    protected static ?string $navigationGroup = 'Gestión de Facturación';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Impuesto')
                    ->schema([
                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: ISV, Impuesto sobre Ventas'),
                            
                        TextInput::make('porcentaje')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->suffix('%')
                            ->placeholder('Ej: 15.00'),
                            
                        Select::make('es_exento')
                            ->required()
                            ->options([
                                'SI' => 'Sí',
                                'NO' => 'No',
                            ])
                            ->default('NO')
                            ->helperText('¿Este impuesto permite exención?'),
                    ])->columns(3),
                    
                Forms\Components\Section::make('Vigencia')
                    ->schema([
                        DatePicker::make('vigente_desde')
                            ->required()
                            ->default(now())
                            ->helperText('Fecha desde la cual es válido este impuesto'),
                            
                        DatePicker::make('vigente_hasta')
                            ->nullable()
                            ->helperText('Dejar vacío si no tiene fecha de vencimiento'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('porcentaje')
                    ->suffix('%')
                    ->sortable()
                    ->alignEnd(),
                    
                TextColumn::make('es_exento')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'SI' => 'success',
                        'NO' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => $state === 'SI' ? 'Permite Exención' : 'No Exento'),
                    
                TextColumn::make('vigente_desde')
                    ->date()
                    ->sortable(),
                    
                TextColumn::make('vigente_hasta')
                    ->date()
                    ->sortable()
                    ->placeholder('Sin vencimiento'),
                    

                TextColumn::make('estado')
                    ->badge()
                    ->getStateUsing(function (Impuesto $record): string {
                        $hoy = now()->toDateString();
                        if ($record->vigente_desde > $hoy) {
                            return 'pendiente';
                        }
                        if ($record->vigente_hasta && $record->vigente_hasta < $hoy) {
                            return 'vencido';
                        }
                        return 'vigente';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'vigente' => 'success',
                        'pendiente' => 'warning',
                        'vencido' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'vigente' => 'Vigente',
                        'pendiente' => 'Pendiente',
                        'vencido' => 'Vencido',
                    }),
                    
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('es_exento')
                    ->options([
                        'SI' => 'Permite Exención',
                        'NO' => 'No Exento',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListImpuestos::route('/'),
            'create' => Pages\CreateImpuestos::route('/create'),
            'edit' => Pages\EditImpuestos::route('/{record}/edit'),
        ];
    }
}
