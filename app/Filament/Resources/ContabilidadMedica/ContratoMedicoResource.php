<?php

namespace App\Filament\Resources\ContabilidadMedica;

use App\Filament\Resources\ContabilidadMedica\ContratoMedicoResource\Pages;
use App\Filament\Resources\ContabilidadMedica\ContratoMedicoResource\RelationManagers;
use App\Models\ContabilidadMedica\ContratoMedico;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;


class ContratoMedicoResource extends Resource
{
    protected static ?string $model = ContratoMedico::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Contabilidad Médica';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('centro_id', \Illuminate\Support\Facades\Auth::user()->centro_id)->count();
    }

    public static function form(Form $form): Form
    {
        $centro_id = \Illuminate\Support\Facades\Auth::user()->centro_id;
        
        // Regla de validación personalizada para asegurar que al menos un tipo de compensación esté definido
        $validarCompensacion = function (string $attribute, $value, $fail) use ($form) {
            $state = $form->getState();
            
            $salarioQuincenal = (float) ($state['salario_quincenal'] ?? 0);
            $salarioMensual = (float) ($state['salario_mensual'] ?? 0);
            $porcentajeServicio = (float) ($state['porcentaje_servicio'] ?? 0);
            
            if ($salarioQuincenal == 0 && $salarioMensual == 0 && $porcentajeServicio == 0) {
                $fail("Debe especificar al menos una forma de compensación: salario fijo o porcentaje por servicios.");
            }
        };
        
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
                    ->label('Salario Quincenal')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->prefix('L.')
                    ->required(false)
                    ->live(onBlur: true)
                    ->rules([$validarCompensacion])
                    ->helperText('Dejar en 0 si el contrato es solo por porcentaje de servicio'),
                    
                Forms\Components\TextInput::make('salario_mensual')
                    ->label('Salario Mensual')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->prefix('L.')
                    ->required(false)
                    ->live(onBlur: true)
                    ->rules([$validarCompensacion])
                    ->helperText('Dejar en 0 si el contrato es solo por porcentaje de servicio'),
                    
                Forms\Components\TextInput::make('porcentaje_servicio')
                    ->label('Porcentaje por Servicios')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->required(false)
                    ->live(onBlur: true)
                    ->rules([$validarCompensacion])
                    ->helperText('Dejar en 0 si el contrato es solo por salario fijo'),
                    
                Forms\Components\DatePicker::make('fecha_inicio')
                    ->required(),
                    
                Forms\Components\DatePicker::make('fecha_fin')
                    ->nullable(),
                
                Forms\Components\Toggle::make('activo')
                    ->inline(false)
                    ->default(true),
                
                // Sección informativa sobre el tipo de contrato
                Forms\Components\Section::make('Tipo de Contrato')
                    ->description('Seleccione al menos una forma de compensación: salario fijo o porcentaje por servicios')
                    ->schema([
                        Forms\Components\Placeholder::make('tipo_contrato_info')
                            ->label('Tipo de Contrato Seleccionado')
                            ->content(function ($get) {
                                $salarioQuincenal = (float) $get('salario_quincenal');
                                $salarioMensual = (float) $get('salario_mensual');
                                $porcentajeServicio = (float) $get('porcentaje_servicio');
                                
                                if ($porcentajeServicio > 0 && $salarioQuincenal == 0 && $salarioMensual == 0) {
                                    return '✅ Contrato solo por porcentaje de servicio ('.$porcentajeServicio.'%)';
                                } elseif ($porcentajeServicio == 0 && ($salarioQuincenal > 0 || $salarioMensual > 0)) {
                                    return '✅ Contrato solo por salario fijo';
                                } elseif ($porcentajeServicio > 0 && ($salarioQuincenal > 0 || $salarioMensual > 0)) {
                                    return '✅ Contrato mixto (salario fijo + '.$porcentajeServicio.'% por servicios)';
                                } else {
                                    return '❌ Debe seleccionar al menos una forma de compensación';
                                }
                            }),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $centro_id = \Illuminate\Support\Facades\Auth::user()->centro_id;
        
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
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state > 0 ? "L. " . number_format($state, 2) : "N/A"),
                    
                Tables\Columns\TextColumn::make('porcentaje_servicio')
                    ->suffix('%')
                    ->formatStateUsing(fn ($state) => $state > 0 ? $state : "N/A"),
                
                Tables\Columns\TextColumn::make('tipo_contrato')
                    ->label('Tipo de Contrato')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, 'Solo por porcentaje') => 'success',
                        str_contains($state, 'Solo por salario') => 'info',
                        str_contains($state, 'Mixto') => 'warning',
                        default => 'gray',
                    }),
                    
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
            //
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
        $user = Auth::user();
        
        // Filtrar por centro médico
        $query = $query->where('centro_id', $user->centro_id);
        
        // Obtener el médico vinculado al usuario actual si existe
        $medico = $user->medico;
        
        // Verificar si el usuario está autorizado como médico
        if ($medico && \Spatie\Permission\Models\Role::where('name', 'medico')->whereHas('users', function($q) use ($user) {
            $q->where('model_id', $user->id);
        })->exists()) {
            // Si es médico, filtrar solo sus contratos
            $query = $query->where('medico_id', $medico->id);
        }
        
        return $query->latest('id');
    }
}