<?php

namespace App\Filament\Resources\CentrosMedico;

use App\Filament\Resources\CentrosMedico\CentrosMedicoResource\Pages;
use App\Filament\Resources\CentrosMedico\CentrosMedicoResource\RelationManagers;
use App\Models\Centros_Medico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CentrosMedicoResource extends Resource
{
    protected static ?string $model = Centros_Medico::class;

    protected static ?string $modelLabel = 'Centro Médico';

    /*public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('Crear CentroMedico');
    }*/
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nombre_centro')->required()->maxLength(255),
            Forms\Components\TextInput::make('direccion')->required()->maxLength(255),
            Forms\Components\TextInput::make('telefono')->required()->tel(),
            Forms\Components\FileUpload::make('fotografia')->image()->directory('centros_medicos'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('nombre_centro')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('direccion')->limit(30),
            Tables\Columns\TextColumn::make('telefono'),
            Tables\Columns\ImageColumn::make('fotografia')->circular(),
        ])
        ->filters([])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->actions([
            Tables\Actions\ViewAction::make(), // Opcional
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);

    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public function medicos()
    {
        return $this->belongsToMany(
            \App\Models\Medico::class,
            'centros_medicos_medico',
            'centro_medico_id',
            'medico_id'
        )->withPivot('horario')->withTimestamps();
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCentrosMedicos::route('/'),
            'create' => Pages\CreateCentrosMedico::route('/create'),
            'edit' => Pages\EditCentrosMedico::route('/{record}/edit'),
        ];
    }
    
}
