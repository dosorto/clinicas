<?php

namespace App\Filament\Resources\CentrosMedicosMedico;

use App\Filament\Resources\CentrosMedicosMedico\CentrosMedicosMedicoResource\Pages;
use App\Filament\Resources\CentrosMedicosMedico\CentrosMedicosMedicoResource\RelationManagers;
use App\Models\Centros_Medicos_Medico;
use Filament\Forms;
use App\Models\Medico;
use App\Models\Centros_Medico;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CentrosMedicosMedicoResource extends Resource
{
    protected static ?string $model = Centros_Medicos_Medico::class;

    protected static ?string $modelLabel = 'Médico en Centros Médicos';

    /*public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('crear MedicoCentroMedico');
    }*/

    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('medico_id')
            ->label('Médico')
            ->options(
                Medico::with('persona')->get()->mapWithKeys(function ($medico) {
                    if (!$medico->persona) return [];
                    return [
                        $medico->id => optional($medico->persona)->primer_nombre . ' ' . optional($medico->persona)->primer_apellido
                    ];
                })->filter()
            )
            ->searchable()
            ->required(),

            Forms\Components\Select::make('centro_medico_id')
                ->label('Centro Médico')
                ->options(Centros_Medico::pluck('nombre_centro', 'id'))
                ->searchable()
                ->required(),

            Forms\Components\TimePicker::make('horario_entrada')
                ->label('Horario de Entrada')
                ->seconds(false)
                ->required(),

            Forms\Components\TimePicker::make('horario_salida')
                ->label('Horario de Salida')
                ->seconds(false)
                ->required(),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('medico.persona.primer_nombre')
                ->label('Médico')
                ->formatStateUsing(function ($state, $record) {
                    if (!$record->medico?->persona) return 'No definido';
                    return $record->medico->persona->primer_nombre . ' ' . $record->medico->persona->primer_apellido;
                })
                ->searchable(query: function (Builder $query, string $search) {
                    return $query->whereHas('medico.persona', function ($query) use ($search) {
                        $query->where('primer_nombre', 'like', "%{$search}%")
                              ->orWhere('primer_apellido', 'like', "%{$search}%");
                    });
                }),

            Tables\Columns\TextColumn::make('centro_medico_id')
                ->label('Centro Médico')
                ->formatStateUsing(function ($state) {
                    $centro = \App\Models\Centros_Medico::find($state);
                    return $centro?->nombre_centro ?? 'No definido';
                }),

            Tables\Columns\TextColumn::make('horario_entrada')
                ->label('Hora de Entrada')
                ->time(),

            Tables\Columns\TextColumn::make('horario_salida')
                ->label('Hora de Salida')
                ->time(),

        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListCentrosMedicosMedicos::route('/'),
            'create' => Pages\CreateCentrosMedicosMedico::route('/create'),
            'edit' => Pages\EditCentrosMedicosMedico::route('/{record}/edit'),
        ];
        
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['medico.persona', 'centro_medico'])
            ->whereHas('medico.persona')
            ->whereHas('centro_medico');
    }
}
