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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Textarea;

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
                Wizard::make([
                    Wizard\Step::make('Información Personal')
                        ->schema([
                            TextInput::make('persona.dni')
                                ->label('DNI')
                                ->required()
                                ->unique('personas', 'dni', ignoreRecord: true)
                                ->maxLength(20),

                            TextInput::make('persona.primer_nombre')
                                ->label('Primer Nombre')
                                ->required()
                                ->maxLength(255),
                            
                            TextInput::make('persona.segundo_nombre')
                                ->label('Segundo Nombre')
                                ->maxLength(255),
                            
                            TextInput::make('persona.primer_apellido')
                                ->label('Primer Apellido')
                                ->required()
                                ->maxLength(255),
                            
                            TextInput::make('persona.segundo_apellido')
                                ->label('Segundo Apellido')
                                ->maxLength(255),
                            
                            TextInput::make('persona.telefono')
                                ->label('Teléfono')
                                ->tel()
                                ->required()
                                ->maxLength(20),
                            
                            Textarea::make('persona.direccion')
                                ->label('Dirección')
                                ->rows(3)
                                ->required()
                                ->columnSpanFull(),
                            
                            Select::make('persona.sexo')
                                ->label('Sexo')
                                ->options([
                                    'M' => 'Masculino',
                                    'F' => 'Femenino'
                                ])
                                ->required(),
                            
                            DatePicker::make('persona.fecha_nacimiento')
                                ->label('Fecha de Nacimiento')
                                ->required()
                                ->maxDate(now()->subYears(18)),
                            
                            Select::make('persona.nacionalidad_id')
                                ->label('Nacionalidad')
                                ->relationship('persona.nacionalidad', 'nacionalidad')
                                ->preload()
                                ->searchable()
                                ->required(),
                            
                            FileUpload::make('persona.fotografia')
                                ->label('Fotografía')
                                ->image()
                                ->directory('personas')
                                ->maxSize(2048)
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                    
                    Wizard\Step::make('Información de Usuario')
                        ->schema([
                            TextInput::make('name')
                                ->label('Nombre de Usuario')
                                ->required()
                                ->maxLength(255),
                            
                            TextInput::make('email')
                                ->label('Email')
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                            
                            TextInput::make('password')
                                ->label('Contraseña')
                                ->password()
                                ->required(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                                ->dehydrateStateUsing(fn($state) => !empty($state) ? bcrypt($state) : null)
                                ->dehydrated(fn($state) => filled($state))
                                ->minLength(8),
                            
                            TextInput::make('password_confirmation')
                                ->label('Confirmar Contraseña')
                                ->password()
                                ->required(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                                ->same('password')
                                ->dehydrated(false),
                            
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
                                ->visible(fn () => auth()->user()?->hasRole('root'))
                                ->default(fn () => auth()->user()?->centro_id),
                        ])
                        ->columns(2),
                ])
                ->columnSpanFull()
                ->persistStepInQueryString()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre de Usuario')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('persona.nombre_completo')
                    ->label('Persona')
                    ->getStateUsing(fn ($record) => $record->persona ? 
                        $record->persona->primer_nombre . ' ' . $record->persona->primer_apellido : 
                        'Sin persona'
                    )
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('persona', function ($q) use ($search) {
                            $q->where('primer_nombre', 'like', "%{$search}%")
                              ->orWhere('primer_apellido', 'like', "%{$search}%");
                        });
                    }),
                
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->limit(2),
                
                Tables\Columns\TextColumn::make('centro.nombre_centro')
                    ->label('Centro Médico')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['persona', 'roles', 'centro']);
    }
}
