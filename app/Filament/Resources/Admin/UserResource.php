<?php

namespace App\Filament\Resources\Admin;

use App\Filament\Resources\Admin\UserResource\Pages;
use App\Filament\Resources\Admin\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Althinect\FilamentSpatieRolesPermissions\Forms\Components\RoleSelect;
use Althinect\FilamentSpatieRolesPermissions\Forms\Components\PermissionSelect;
use Filament\Forms\Components\DatePicker;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;

class UserResource extends Resource
{
    public static function shouldRegisterNavigation(): bool
    {
    return auth()->user()?->can('crear usuario');
    }
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            TextInput::make('name')->required(),
            TextInput::make('email')->email()->required(),
            TextInput::make('password')
                ->password()
                ->required(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                ->dehydrateStateUsing(fn($state) => !empty($state) ? bcrypt($state) : null)
                ->dehydrated(fn($state) => filled($state))
                ->label('Password'),
            Select::make('persona_id')
                ->label('Persona')
                   ->relationship(
                    name: 'persona',
                    titleAttribute: 'primer_nombre', // Usa un campo real para búsqueda y orden
                    modifyQueryUsing: fn ($query) => $query->select(['id', 'primer_nombre', 'primer_apellido'])
                )
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->primer_nombre . ' ' . $record->primer_apellido)
                ->searchable()
                
                 ->createOptionForm([
                    TextInput::make('primer_nombre')->label('Primer Nombre')->required(),
                    TextInput::make('segundo_nombre')->label('Segundo Nombre'),
                    TextInput::make('primer_apellido')->label('Apellido')->required(),
                    TextInput::make('segundo_apellido')->label('Segundo Apellido'),
                    TextInput::make('dni')->label('DNI')->required()->unique(),
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
                        ->nullable(),
                    ])
                ->createOptionAction(function ($action) {
                return $action->mutateFormDataUsing(function (array $data) {
                $data['created_by'] = Filament::auth()->id() ?? auth()->id();
                return $data;
                    });
                }),
            Select::make('roles')
                ->label('Roles')
                ->multiple()
                ->relationship('roles', 'name')
                ->preload()
                ->required(),
            
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
            Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable(),
            Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
            Tables\Columns\TextColumn::make('persona.primer_nombre')->label('Persona'),
            Tables\Columns\TextColumn::make('roles.name')->label('Roles')->badge()->limit(2),
            ])
            ->filters([
                //
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
