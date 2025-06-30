<?php

namespace App\Filament\Resources\Especialidad;

use App\Filament\Resources\Especialidad\EspecialidadResource\Pages;
use App\Filament\Resources\Especialidad\EspecialidadResource\RelationManagers;
use App\Models\Especialidad;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use Filament\Actions\Action; 
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EspecialidadResource extends Resource
{
    protected static ?string $navigationLabel = 'Especialidades';
    protected static ?string $modelLabel = 'Especialidad';
    // protected static ?string $navigationGroup = 'Configuraciones'; // Opcional: agrupa en el sidebar
    protected static ?string $createButtonLabel = 'Crear Especialidad';
    protected static ?string $pluralModelLabel = 'Especialidades';

    protected static ?string $model = Especialidad::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\TextInput::make('especialidad')
                ->label('Nombre de la Especialidad')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true) // Evita duplicados al editar
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
                            
            Tables\Columns\TextColumn::make('especialidad')
                ->label('Especialidad Médica')
                ->sortable()
                ->searchable(),
                
            Tables\Columns\TextColumn::make('created_at')
                ->label('Creado')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            // Filtros opcionales
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
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

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label('Crear Especialidad') // Texto personalizado del botón
            ->submit('create')
            ->keyBindings(['mod+s']);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Crear Especialidad') // Texto alternativo
                ->icon('heroicon-o-plus') // Ícono opcional
        ];
    }




    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEspecialidades::route('/'),
            'create' => Pages\CreateEspecialidad::route('/create'),
            'edit' => Pages\EditEspecialidad::route('/{record}/edit'),
        ];
    }
}
