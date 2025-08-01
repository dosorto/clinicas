<?php

namespace App\Filament\Resources\CAICorrelativos;

use App\Filament\Resources\CAICorrelativos\CAICorrelativosResource\Pages;
use App\Filament\Resources\CAICorrelativos\CAICorrelativosResource\RelationManagers;
use App\Models\CAI_Correlativos;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CAICorrelativosResource extends Resource
{
    protected static ?string $model = CAI_Correlativos::class;

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
            'index' => Pages\ListCAICorrelativos::route('/'),
            'create' => Pages\CreateCAICorrelativos::route('/create'),
            'edit' => Pages\EditCAICorrelativos::route('/{record}/edit'),
        ];
    }
}
