<?php

namespace App\Filament\Resources\Descuentos;

use App\Filament\Resources\Descuentos\DescuentosResource\Pages;
use App\Filament\Resources\Descuentos\DescuentosResource\RelationManagers;
use App\Models\Descuento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DescuentosResource extends Resource
{
    protected static ?string $model = Descuento::class;

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
            'index' => Pages\ListDescuentos::route('/'),
            'create' => Pages\CreateDescuentos::route('/create'),
            'edit' => Pages\EditDescuentos::route('/{record}/edit'),
        ];
    }
}
