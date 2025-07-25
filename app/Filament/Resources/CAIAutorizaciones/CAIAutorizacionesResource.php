<?php

namespace App\Filament\Resources\CAIAutorizaciones;

use App\Filament\Resources\CAIAutorizaciones\CAIAutorizacionesResource\Pages;
use App\Filament\Resources\CAIAutorizaciones\CAIAutorizacionesResource\RelationManagers;
use App\Models\CAI_Autorizaciones;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CAIAutorizacionesResource extends Resource
{
    protected static ?string $model = CAI_Autorizaciones::class;

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
            'index' => Pages\ListCAIAutorizaciones::route('/'),
            'create' => Pages\CreateCAIAutorizaciones::route('/create'),
            'edit' => Pages\EditCAIAutorizaciones::route('/{record}/edit'),
        ];
    }
}
