<?php

namespace App\Filament\Resources\PagosFacturas;

use App\Filament\Resources\PagosFacturas\PagosFacturasResource\Pages;
use App\Filament\Resources\PagosFacturas\PagosFacturasResource\RelationManagers;
use App\Models\Pagos_Factura;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PagosFacturasResource extends Resource
{
    protected static ?string $model = Pagos_Factura::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Gestión de Facturación';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('factura_id')
                    ->label('Factura')
                    ->options(
                        \App\Models\Factura::query()
                            ->latest()
                            ->pluck('numero_factura', 'id')   // `numero_factura` es accesor → ok
                    )
                    ->searchable()
                    ->required(),

                Select::make('tipo_pago_id')
                    ->label('Tipo de Pago')
                    ->relationship('tipoPago', 'nombre')     // ahora sí existe la relación
                    ->required(),

                Forms\Components\TextInput::make('monto_recibido')
                    ->numeric()->required()->prefix('L.'),
                Forms\Components\Select::make('tipo_pago_id')
                    ->relationship('tipoPago','nombre')
                    ->required(),
                Forms\Components\DatePicker::make('fecha_pago')
                    ->default(now())->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('factura.numero_factura')->label('Factura'),
                TextColumn::make('tipoPago.nombre')->label('Tipo de Pago'),
                TextColumn::make('monto_recibido')->money('HNL')->alignEnd(),
                TextColumn::make('fecha_pago')->dateTime(),
            ])
            ->defaultSort('fecha_pago','desc');
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
            'index' => Pages\ListPagosFacturas::route('/'),
            'create' => Pages\CreatePagosFacturas::route('/create'),
            'edit' => Pages\EditPagosFacturas::route('/{record}/edit'),
            //'view'   => Pages\ViewPagosFacturas::route('/{record}'),
        ];
    }
}
