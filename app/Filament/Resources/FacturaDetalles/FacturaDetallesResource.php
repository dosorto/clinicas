<?php

namespace App\Filament\Resources\FacturaDetalles;

use App\Filament\Resources\FacturaDetalles\FacturaDetallesResource\Pages;
use App\Filament\Resources\FacturaDetalles\FacturaDetallesResource\RelationManagers;
use App\Models\FacturaDetalle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FacturaDetallesResource extends Resource
{
    protected static ?string $model = FacturaDetalle::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFacturaDetalles::route('/'),
            'create' => Pages\CreateFacturaDetalles::route('/create'),
            'edit' => Pages\EditFacturaDetalles::route('/{record}/edit'),
        ];
    }
}
