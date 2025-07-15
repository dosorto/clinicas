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
    protected static ?string $navigationGroup = 'Gestión de Seguridad';

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

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
                ->preload()
                ->searchable()
                ->required(),
            Select::make('roles')
                ->label('Roles')
                ->multiple()
                ->relationship('roles', 'name')
                ->preload()
                ->required(),
            Select::make('centro_id')
                ->label('Centro Médico')
                ->options(\App\Models\Centros_Medico::pluck('nombre_centro', 'id'))
                ->required()
                ->visible(fn () => auth()->user()?->hasRole('root')) // Solo visible para root, por ejemplo
            
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
