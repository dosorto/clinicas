<?php

namespace App\Filament\Resources\CuentasPorCobrar;

use App\Filament\Resources\CuentasPorCobrar\CuentasPorCobrarResource\Pages;
use App\Filament\Resources\CuentasPorCobrar\CuentasPorCobrarResource\RelationManagers;
use App\Models\Cuentas_Por_Cobrar;
use CurlHandle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CuentasPorCobrarResource extends Resource
{
    protected static ?string $model = Cuentas_Por_Cobrar::class;

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
            'index' => Pages\ListCuentasPorCobrars::route('/'),
            'create' => Pages\CreateCuentasPorCobrar::route('/create'),
            'edit' => Pages\EditCuentasPorCobrar::route('/{record}/edit'),
        ];
    }
}
