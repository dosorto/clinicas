<?php

namespace App\Filament\Resources\ContabilidadMedica;

use App\Filament\Resources\ContabilidadMedica\NominaResource\Pages;
use App\Models\ContabilidadMedica\Nomina;
use App\Models\Medico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class NominaResource extends Resource
{
    protected static ?string $model = Nomina::class;

    protected static ?string $navigationGroup = 'Contabilidad Médica';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Nóminas';
    protected static ?string $modelLabel = 'Nómina';
    protected static ?string $pluralModelLabel = 'Nóminas';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información General')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('empresa')
                    ->label('Centro Médico')
                    ->default(function () {
                        $user = Auth::user();
                        if ($user && $user->centro) {
                            return $user->centro->nombre_centro;
                        }
                        return '';
                    })
                    ->required()
                    ->maxLength(255),

                                TextInput::make('año')
                                    ->label('Año')
                                    ->required()
                                    ->numeric()
                                    ->default(date('Y'))
                                    ->minValue(2020)
                                    ->maxValue(2030),

                                Select::make('mes')
                                    ->label('Mes')
                                    ->options([
                                        1 => 'Enero',
                                        2 => 'Febrero',
                                        3 => 'Marzo',
                                        4 => 'Abril',
                                        5 => 'Mayo',
                                        6 => 'Junio',
                                        7 => 'Julio',
                                        8 => 'Agosto',
                                        9 => 'Septiembre',
                                        10 => 'Octubre',
                                        11 => 'Noviembre',
                                        12 => 'Diciembre',
                                    ])
                                    ->required()
                                    ->default(date('n')),

                                Select::make('tipo_pago')
                                    ->label('Tipo de Pago')
                                    ->options([
                                        'mensual' => 'Mensual',
                                        'quincenal' => 'Quincenal',
                                        'semanal' => 'Semanal',
                                    ])
                                    ->required()
                                    ->default('mensual'),
                            ]),

                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),

                Section::make('Médicos en Nómina')
                    ->schema([
                        Repeater::make('medicos_nomina')
                            ->label('Seleccionar Médicos')
                            ->relationship()
                            ->schema([
                                Checkbox::make('seleccionado')
                                    ->label('Incluir en nómina')
                                    ->default(true),

                                Select::make('medico_id')
                                    ->label('Médico')
                                    ->relationship('medico', 'id')
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->persona->nombre_completo ?? 'Sin nombre')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                TextInput::make('medico_nombre')
                                    ->label('Nombre del Médico')
                                    ->required()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        if (!$state && $get('medico_id')) {
                                            $medico = Medico::find($get('medico_id'));
                                            if ($medico && $medico->persona) {
                                                $set('medico_nombre', $medico->persona->nombre_completo);
                                            }
                                        }
                                    }),

                                TextInput::make('salario_base')
                                    ->label('Salario Base')
                                    ->numeric()
                                    ->prefix('L.')
                                    ->required()
                                    ->default(function (Forms\Get $get) {
                                        if ($get('medico_id')) {
                                            $medico = Medico::find($get('medico_id'));
                                            return $medico?->contratos?->first()?->salario_mensual ?? 0;
                                        }
                                        return 0;
                                    }),

                                TextInput::make('deducciones')
                                    ->label('Deducciones')
                                    ->numeric()
                                    ->prefix('L.')
                                    ->default(0),

                                TextInput::make('percepciones')
                                    ->label('Percepciones')
                                    ->numeric()
                                    ->prefix('L.')
                                    ->default(0),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel('Agregar Médico'),
                    ])
                    ->collapsed()
                    ->persistCollapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('año')
                    ->label('Año')
                    ->sortable(),

                TextColumn::make('mes')
                    ->label('Mes')
                    ->formatStateUsing(function ($state) {
                        $meses = [
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                        ];
                        return $meses[(int)$state] ?? $state;
                    })
                    ->sortable(),

                TextColumn::make('tipo_pago')
                    ->label('Tipo de Pago')
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'mensual' => 'Mensual',
                            'quincenal' => 'Quincenal',
                            'semanal' => 'Semanal',
                            default => ucfirst($state)
                        };
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'mensual' => 'success',
                        'quincenal' => 'warning',
                        'semanal' => 'info',
                        default => 'gray',
                    }),

                IconColumn::make('cerrada')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('danger')
                    ->falseColor('success'),

                TextColumn::make('numero_medicos')
                    ->label('Médicos')
                    ->getStateUsing(fn ($record) => $record->numero_empleados)
                    ->badge(),

                TextColumn::make('total_nomina')
                    ->label('Total')
                    ->getStateUsing(fn ($record) => 'L. ' . number_format($record->total_nomina, 2))
                    ->color('success'),

                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('año')
                    ->options(function () {
                        $currentYear = date('Y');
                        $years = [];
                        for ($i = $currentYear - 2; $i <= $currentYear + 1; $i++) {
                            $years[$i] = $i;
                        }
                        return $years;
                    }),

                Tables\Filters\SelectFilter::make('mes')
                    ->options([
                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                    ]),

                Tables\Filters\SelectFilter::make('tipo_pago')
                    ->options([
                        'mensual' => 'Mensual',
                        'quincenal' => 'Quincenal',
                        'semanal' => 'Semanal',
                    ]),

                Tables\Filters\TernaryFilter::make('cerrada')
                    ->label('Estado')
                    ->placeholder('Todas')
                    ->trueLabel('Cerradas')
                    ->falseLabel('Abiertas'),
            ])
            ->actions([
                ViewAction::make()
                    ->icon('heroicon-o-eye'),

                EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->visible(fn (Nomina $record): bool => !$record->cerrada),

                Tables\Actions\Action::make('cerrar')
                    ->label('Cerrar')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->visible(fn (Nomina $record): bool => !$record->cerrada)
                    ->requiresConfirmation()
                    ->modalHeading('Cerrar Nómina')
                    ->modalDescription('Una vez cerrada la nómina, no podrás editarla ni eliminarla. ¿Estás seguro?')
                    ->modalSubmitActionLabel('Sí, cerrar nómina')
                    ->action(function (Nomina $record) {
                        $record->cerrar();
                    }),

                Tables\Actions\Action::make('generar_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(fn (Nomina $record) => route('nomina.pdf', $record))
                    ->openUrlInNewTab(),

                DeleteAction::make()
                    ->visible(fn (Nomina $record): bool => !$record->cerrada),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['detalles.medico.persona']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNominas::route('/'),
            'create' => Pages\CreateNomina::route('/create'),
            'view' => Pages\ViewNomina::route('/{record}'),
            'edit' => Pages\EditNomina::route('/{record}/edit'),
        ];
    }
}
