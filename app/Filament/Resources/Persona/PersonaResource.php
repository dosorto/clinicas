<?php

namespace App\Filament\Resources\Persona;

use App\Filament\Resources\Persona\PersonaResource\Pages;
use App\Filament\Resources\Persona\PersonaResource\RelationManagers;
use App\Models\Persona;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;



class PersonaResource extends Resource
{
    public static function shouldRegisterNavigation(): bool
    {
    return auth()->user()?->can('crear personas');
    }
    
    protected static ?string $model = Persona::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('primer_nombre')->label('Primer Nombre')->required(),
                    TextInput::make('segundo_nombre')->label('Segundo Nombre'),
                    TextInput::make('primer_apellido')->label('Primer Apellido')->required(),
                    TextInput::make('segundo_apellido')->label('Segundo Apellido'),
                    TextInput::make('dni')->label('DNI')->required()->unique(ignoreRecord: true),
                    TextInput::make('telefono')->label('Teléfono')->required(),
                    TextInput::make('direccion')->label('Dirección')->required(),
                    Select::make('sexo')
                        ->label('Sexo')
                        ->options([
                            'M' => 'Masculino',
                            'F' => 'Femenino',
                        ])
                        ->required(),
                    DatePicker::make('fecha_nacimiento')->label('Fecha de Nacimiento')->date()->required(),
                    Select::make('nacionalidad_id')
                        ->label('Nacionalidad')
                        ->relationship('nacionalidad', 'nacionalidad')
                        ->required(),
                    FileUpload::make('fotografia')
                        ->label('Fotografía')
                        ->image()
                        ->directory('personas')
                        ->maxSize(2048)
                        ->deleteUploadedFileUsing(fn ($file) => Storage::disk('public')->delete($file))
                        ->nullable(),    
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('primer_nombre')->label('Primer Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('primer_apellido')->label('Primer Apellido')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('dni')->label('DNI')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('telefono')->label('Teléfono')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('direccion')->label('Dirección')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('nacionalidad.nacionalidad')->label('Nacionalidad')->searchable()->sortable(),
                
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                
                

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
            'index' => Pages\ListPersonas::route('/'),
            'create' => Pages\CreatePersona::route('/create'),
            'edit' => Pages\EditPersona::route('/{record}/edit'),
        ];
    }

    // Controlar quién puede eliminar según permiso
    public static function canDelete(
        \Illuminate\Database\Eloquent\Model $record
    ): bool {
        return auth()->user()?->can('borrar personas');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('borrar personas');
    }
}
