<?php

namespace App\Filament\Resources\Medico;

use App\Filament\Resources\Medico\MedicoResource\Pages;
use App\Filament\Resources\Medico\MedicoResource\RelationManagers;
use App\Models\Medico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MedicoResource extends Resource
{
    
    protected static ?string $model = Medico::class;


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Select::make('persona_id')
                ->label('Persona')
                ->relationship('persona', 'primer_nombre')
                ->searchable()
                ->preload()
                ->required(),
                
            Forms\Components\TextInput::make('numero_colegiacion')
                ->label('Número de Colegiación')
                ->required()
                ->maxLength(50),
        ]);
    }

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('persona.primer_nombre')
                ->label('Nombre Completo')
                ->getStateUsing(fn ($record) => $record->persona->primer_nombre.' '.$record->persona->primer_apellido)
                ->searchable(['primer_nombre', 'primer_apellido']),
                
            Tables\Columns\TextColumn::make('numero_colegiacion')
                ->label('N° Colegiación')
                ->searchable(),
        ])
        ->filters([
            //
        ])
        ->actions([
            Tables\Actions\EditAction::make()
                ->icon('heroicon-o-pencil') // Icono de edición
                ->color('primary'),
                
            Tables\Actions\DeleteAction::make()
                ->icon('heroicon-o-trash') // Icono de borrado
                ->color('danger'),
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
            'index' => Pages\ListMedicos::route('/'),
            'create' => Pages\CreateMedico::route('/create'),
            'edit' => Pages\EditMedico::route('/{record}/edit'),
        ];
    }
};