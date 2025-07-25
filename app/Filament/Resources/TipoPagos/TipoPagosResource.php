<?php

namespace App\Filament\Resources\TipoPagos;

use App\Filament\Resources\TipoPagos\TipoPagosResource\Pages;
use App\Filament\Resources\TipoPagos\TipoPagosResource\RelationManagers;
use App\Models\Tipo_Pago;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TipoPagosResource extends Resource
{
    protected static ?string $model = Tipo_Pago::class;

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
            'index' => Pages\ListTipoPagos::route('/'),
            'create' => Pages\CreateTipoPagos::route('/create'),
            'edit' => Pages\EditTipoPagos::route('/{record}/edit'),
        ];
    }
}
