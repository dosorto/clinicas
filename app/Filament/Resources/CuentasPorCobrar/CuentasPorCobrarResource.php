<?php

namespace App\Filament\Resources\CuentasPorCobrar;

use App\Filament\Resources\CuentasPorCobrar\CuentasPorCobrarResource\Pages;
use App\Filament\Resources\CuentasPorCobrar\CuentasPorCobrarResource\RelationManagers;
use App\Models\Cuentas_Por_Cobrar;
use CurlHandle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CuentasPorCobrarResource extends Resource
{
    protected static ?string $model = Cuentas_Por_Cobrar::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
    protected static ?string $navigationGroup = 'Gestión de Facturación';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('factura.numero_factura')->label('Factura'),
                TextColumn::make('paciente.persona.nombre_completo')->label('Paciente'),
                TextColumn::make('saldo_pendiente')->money('HNL')->alignEnd()->color('danger'),
                TextColumn::make('fecha_vencimiento')->date()->sortable(),
                TextColumn::make('estado_cuentas_por_cobrar')
                    ->badge()
                    ->color(fn($s)=>match($s){'ABIERTA'=>'warning','VENCIDA'=>'danger','CERRADA'=>'success','INC incobrable'=>'gray'}),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado_cuentas_por_cobrar')
                    ->options([
                        'ABIERTA'=>'Abiertas','VENCIDA'=>'Vencidas',
                        'CERRADA'=>'Cerradas','INC incobrable'=>'Incobrables']),
            ])
            ->defaultSort('fecha_vencimiento');
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
            'index' => Pages\ListCuentasPorCobrars::route('/'),
            'create' => Pages\CreateCuentasPorCobrar::route('/create'),
            'edit' => Pages\EditCuentasPorCobrar::route('/{record}/edit'),
        ];
    }
}
