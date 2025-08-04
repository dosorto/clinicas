<?php

namespace App\Filament\Resources\Citas;

use App\Filament\Resources\Citas\CitasResource\Pages;
use App\Models\Citas;
use App\Models\Medico;
use App\Models\Pacientes;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\CreateAction;
use App\Filament\Resources\Citas\CitasResource\Pages\CreateCitas;
use Filament\Pages\Page;
class CitasResource extends Resource
{
    protected static ?string $model = Citas::class;

    // Etiquetas en español
    protected static ?string $navigationLabel = 'Citas';
    protected static ?string $modelLabel      = 'Cita';
    protected static ?string $navigationGroup = 'Gestión Médica';
    protected static ?string $pluralModelLabel = 'Citas';

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

public static function form(Form $form): Form
{
    return $form
        ->schema([
            // Campo oculto con el ID del médico autenticado
            Forms\Components\Hidden::make('medico_id')
                ->default(fn () => auth()->user()?->medico?->id ?? null)
                ->required(),

            // Opcional: Mostrar el nombre del médico en solo lectura
            Forms\Components\Placeholder::make('medico_nombre')
                ->label('Médico')
                ->content(fn () => auth()->user()?->medico?->persona?->primer_nombre . ' ' . auth()->user()?->medico?->persona?->primer_apellido),

            Select::make('paciente_id')
                ->label('Paciente')
                ->options(function () {
                    $query = Pacientes::with('persona');
                    if (!auth()->user()?->hasRole('root')) {
                        $query->where('centro_id', session('current_centro_id'));
                    }

                    return $query->get()
                        ->mapWithKeys(fn($p) => [
                            $p->id => "{$p->persona->primer_nombre} {$p->persona->primer_apellido}",
                        ]);
                })
                ->searchable()
                ->required(),

            DatePicker::make('fecha')
                ->label('Fecha de la cita')
                ->required(),

            TimePicker::make('hora')
                ->label('Hora de la cita')
                ->required()
                ->seconds(false),

            Textarea::make('motivo')
                ->label('Motivo de la cita')
                ->rows(3),

            Select::make('estado')
                ->label('Estado')
                ->default('Pendiente')
                ->disabled(fn ($record) => is_null($record)) // solo bloqueado en create
                ->options([
                    'Pendiente'  => 'Pendiente',
                    'Confirmado' => 'Confirmado',
                    'Cancelado'  => 'Cancelado',
                    'Realizada'  => 'Realizada',
                ])
                ->visible(fn (Page $livewire) => !($livewire instanceof CreateCitas)) 
                ->required(),
        ]);
}


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('medico.persona.primer_nombre')
                    ->label('Médico')
                    ->formatStateUsing(fn($state, $record) => "{$record->medico->persona->primer_nombre} {$record->medico->persona->primer_apellido}")
                    ->searchable()
                    ->sortable(),

                TextColumn::make('paciente.persona.primer_nombre')
                    ->label('Paciente')
                    ->formatStateUsing(fn($state, $record) => "{$record->paciente->persona->primer_nombre} {$record->paciente->persona->primer_apellido}")
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),

                TextColumn::make('hora')
                    ->label('Hora')
                    ->time()
                    ->sortable(),

                TextColumn::make('motivo')
                    ->label('Motivo')
                    ->wrap()
                    ->limit(50), // recorta si es muy largo

                BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'Pendiente'  => 'warning',
                        'Confirmado' => 'primary',
                        'Cancelado'  => 'danger',
                        'Realizada'  => 'success',
                    ])
                    
                    ->extraAttributes(['class' => 'px-2 py-1 text-xs']) // badge más compacto
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->label('Filtrar por estado')
                    ->options([
                        'Pendiente'  => 'Pendiente',
                        'Confirmado' => 'Confirmado',
                        'Cancelado'  => 'Cancelado',
                        'Realizada'  => 'Realizada',
                    ]),
            
            ])
           ->actions([
            ActionGroup::make([
                ViewAction::make(),

                EditAction::make(),

                Action::make('confirmar')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Citas $record, $action) => $record->update(['estado' => 'Confirmado']))
                    // ← sólo si está aún pendiente
                    ->visible(fn (Citas $record) => $record->estado === 'Pendiente'),

                Action::make('cancelar')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Citas $record, $action) => $record->update(['estado' => 'Cancelado']))
                    // ← sólo si está aún pendiente
                    ->visible(fn (Citas $record) => $record->estado === 'Pendiente'),

                DeleteAction::make(),
            ])
            ->label('Acciones')
            ->icon('heroicon-o-ellipsis-horizontal'),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Si no es usuario root, filtrar por centro actual
        if (!auth()->user()?->hasRole('root')) {
            $query->where('centro_id', session('current_centro_id'));
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCitas::route('/'),
            'create' => Pages\CreateCitas::route('/create'),
            'edit'   => Pages\EditCitas::route('/{record}/edit'),
        ];
    }
}
