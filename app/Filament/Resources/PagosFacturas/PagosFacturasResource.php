<?php

namespace App\Filament\Resources\PagosFacturas;

use App\Filament\Resources\PagosFacturas\PagosFacturasResource\Pages;
use App\Filament\Resources\PagosFacturas\PagosFacturasResource\RelationManagers;
use App\Models\Pagos_Factura;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PagosFacturasResource extends Resource
{
    protected static ?string $model = Pagos_Factura::class;

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
            'index' => Pages\ListPagosFacturas::route('/'),
            'create' => Pages\CreatePagosFacturas::route('/create'),
            'edit' => Pages\EditPagosFacturas::route('/{record}/edit'),
        ];
    }
}
