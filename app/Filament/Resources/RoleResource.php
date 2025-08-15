<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Centros_Medico;


class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Gestión de Seguridad';
    protected static ?string $navigationLabel = 'Roles';
    protected static ?string $label = 'Rol';
    protected static ?string $pluralLabel = 'Roles';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Hidden::make('centro_id')
                            ->default(fn() => session('current_centro_id')),
                        TextInput::make('name')
                            ->label('Nombre del Rol')
                            ->required()
                            ->maxLength(255)
                            ->rules([
                                function() {
                                    return function($attribute, $value, $fail) {
                                        $exists = Role::where('name', $value)
                                            ->where('guard_name', 'web')
                                            ->where('centro_id', session('current_centro_id'))
                                            ->where('id', '!=', request()->route('record'))
                                            ->exists();
                                        
                                        if ($exists) {
                                            $fail('Este nombre de rol ya existe en este centro médico.');
                                        }
                                    };
                                }
                            ]),
                        Select::make('guard_name')
                            ->label('Guard')
                            ->options(['web' => 'web'])
                            ->default('web')
                            ->disabled()
                    ])->columns(2),

                Forms\Components\Section::make('Permisos del Sistema')
                    ->description('Seleccione los permisos que desea asignar a este rol')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                CheckboxList::make('permissions')
                                    ->label('Permisos Disponibles')
                                    ->relationship('permissions', 'name')
                                    ->options(function() {
                                        $permissions = Permission::query();
                                        
                                        if (!auth()->user()?->hasRole('root')) {
                                            $permissions->whereNotIn('name', [
                                                //AQUI SE OCULTAN LOS PERMISOS QUE SOLO DEBE VER EL ROOT
                                                'ver personas', 'crear personas', 'actualizar personas', 'borrar personas',
                                                'ver nacionalidad', 'crear nacionalidad', 'actualizar nacionalidad', 'borrar nacionalidad',
                                                'ver centromedico', 'crear centromedico', 'actualizar centromedico', 'borrar centromedico',
                                                'ver medicocentromedico', 'crear medicocentromedico', 'actualizar medicocentromedico', 'borrar medicocentromedico',
                                                'crear especialidad', 'actualizar especialidad', 'borrar especialidad',
                                                'ver especialidadmedicos', 'crear especialidadmedicos', 'actualizar especialidadmedicos', 'borrar especialidadmedicos'
                                            ]);
                                        }

                                        return $permissions->get()
                                            ->sortBy('name')
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->columns(3)
                                    ->columnSpanFull()
                                    ->bulkToggleable()
                                    ->gridDirection('row')
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable(),
                TextColumn::make('centro_id')
                    ->label('Centro Médico')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->formatStateUsing(function ($state) {
                        $centro = Centros_Medico::find($state);
                        return $centro ? $centro->nombre_centro : 'N/A';
                    }),
                TextColumn::make('guard_name')->label('Guard'),
                TextColumn::make('created_at')->label('Creado')->dateTime('d/m/Y H:i'),
            ])
            ->filters([
                // Puedes agregar filtros personalizados aquí
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\RoleResource\Pages\ListRoles::route('/'),
            'create' => \App\Filament\Resources\RoleResource\Pages\CreateRole::route('/create'),
            'edit' => \App\Filament\Resources\RoleResource\Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Si el usuario no es root, excluimos el rol root
        if (!auth()->user()?->hasRole('root')) {
            return $query->where(function ($query) {
                $query->where('name', '!=', 'root')
                      ->where('centro_id', session('current_centro_id'));
            });
        }
        
        return $query;
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user->hasRole('root') || $user->hasRole('administrador');
    }
  
}