<?php

namespace App\Filament\Resources\ContabilidadMedica;

use App\Filament\Resources\ContabilidadMedica\ContratoMedicoResource\Pages;
use App\Filament\Resources\ContabilidadMedica\ContratoMedicoResource\RelationManagers;
use App\Models\ContabilidadMedica\ContratoMedico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ContabilidadMedica\ContratoMedicoResource\RelationManagers\CargosRelationManager;
use App\Filament\Resources\ContabilidadMedica\ContratoMedicoResource\RelationManagers\DetallesRelationManager;


class ContratoMedicoResource extends Resource
{
    protected static ?string $model = ContratoMedico::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Contabilidad Médica';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('centro_id', auth()->user()->centro_id)->count();
    }

    public static function form(Form $form): Form
    {
        $centro_id = auth()->user()->centro_id;
        
        return $form
            ->schema([
                Forms\Components\Select::make('medico_id')
                    ->relationship(
                        'medico',
                        'persona_id',
                        fn ($query) => $query->where('centro_id', $centro_id)
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->persona->nombre_completo)
                    ->searchable()
                    ->preload()
                    ->required(),
                    
                Forms\Components\Hidden::make('centro_id')
                    ->default($centro_id),
                    
                Forms\Components\TextInput::make('salario_quincenal')
                    ->numeric()
                    ->required(),
                    
                Forms\Components\TextInput::make('salario_mensual')
                    ->numeric()
                    ->required(),
                    
                Forms\Components\TextInput::make('porcentaje_servicio')
                    ->numeric()
                    ->default(0)
                    ->suffix('%')
                    ->nullable(),
                    
                Forms\Components\DatePicker::make('fecha_inicio')
                    ->required(),
                    
                Forms\Components\DatePicker::make('fecha_fin')
                    ->nullable()
                    ->clearable(),
                
                Forms\Components\Toggle::make('activo')
                    ->inline(false)
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        $centro_id = auth()->user()->centro_id;
        
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->where('centro_id', $centro_id)
                ->with(['medico.persona', 'centro']))
            ->columns([
                Tables\Columns\TextColumn::make('medico.persona.nombre_completo')
                    ->label('Médico')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('persona', function ($query) use ($search) {
                            $query->where('primer_nombre', 'like', "%{$search}%")
                                  ->orWhere('primer_apellido', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('centro.nombre_centro')
                    ->label('Centro Médico')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('salario_mensual')
                    ->money('HNL')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('porcentaje_servicio')
                    ->suffix('%'),
                    
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->date(),
                    
                Tables\Columns\IconColumn::make('activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('centro_id')
                    ->relationship('centro', 'nombre_centro'),
                    
                Tables\Filters\TernaryFilter::make('activo'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            RelationManagers\CargosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContratoMedico::route('/'),
            'create' => Pages\CreateContratoMedico::route('/create'),
            'edit' => Pages\EditContratoMedico::route('/{record}/edit'),
            'view' => Pages\ViewContratoMedico::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        return $query->where('centro_id', auth()->user()->centro_id)
                    ->latest('id');
    }
}