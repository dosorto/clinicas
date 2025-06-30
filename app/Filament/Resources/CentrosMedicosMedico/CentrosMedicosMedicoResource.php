<?php

namespace App\Filament\Resources\CentrosMedicosMedico;

use App\Filament\Resources\CentrosMedicosMedico\CentrosMedicosMedicoResource\Pages;
use App\Filament\Resources\CentrosMedicosMedico\CentrosMedicosMedicoResource\RelationManagers;
use App\Models\Centros_Medicos_Medico;
use Filament\Forms;
use App\Models\Centros_Medico;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CentrosMedicosMedicoResource extends Resource
{
    protected static ?string $model = Centros_Medicos_Medico::class;

    /*public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('crear CentrosMedicosMedico');
    }*/

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('medico_id')
                ->label('Médico')
                ->relationship('medico', 'id') // Puedes cambiar 'id' por nombre completo si está disponible
                ->searchable()
                ->required(),

            Forms\Components\Select::make('centro_medico_id')
                ->label('Centro Médico')
                ->options(Centros_Medico::pluck('nombre_centro', 'id'))
                ->searchable()
                ->required(),

            Forms\Components\TimePicker::make('horario')
                ->label('Horario')
                ->seconds(false)
                ->required(),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('medico.id')->label('ID Médico'),
            Tables\Columns\TextColumn::make('centro.nombre_centro')->label('Centro Médico'),
            Tables\Columns\TextColumn::make('horario')->limit(50),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCentrosMedicosMedicos::route('/'),
            'create' => Pages\CreateCentrosMedicosMedico::route('/create'),
            'edit' => Pages\EditCentrosMedicosMedico::route('/{record}/edit'),
        ];
        
    }
}
