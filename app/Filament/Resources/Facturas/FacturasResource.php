<?php

namespace App\Filament\Resources\Facturas;

use App\Filament\Resources\Facturas\FacturasResource\Pages;
use App\Filament\Resources\Facturas\FacturasResource\RelationManagers;
use App\Models\Factura;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FacturasResource extends Resource
{
    protected static ?string $model = Factura::class;

    protected static ?string $slug = 'facturas';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Factura')
                    ->schema([
                        Forms\Components\Hidden::make('consulta_id'),
                        
                        Forms\Components\Placeholder::make('consulta_info')
                            ->label('Información de la Consulta')
                            ->content(function (?Factura $record): string {
                                $consultaId = request()->get('consulta_id') ?? $record?->consulta_id;
                                
                                if ($consultaId) {
                                    $consulta = \App\Models\Consulta::with(['paciente.persona', 'medico.persona'])
                                        ->find($consultaId);
                                    
                                    if ($consulta) {
                                        return "**Consulta:** {$consulta->id}\n" .
                                               "**Paciente:** {$consulta->paciente->persona->nombres} {$consulta->paciente->persona->apellidos}\n" .
                                               "**Médico:** {$consulta->medico->persona->nombres} {$consulta->medico->persona->apellidos}\n" .
                                               "**Fecha:** {$consulta->created_at->format('d/m/Y')}";
                                    }
                                }
                                
                                return 'No hay consulta asociada';
                            })
                            ->columnSpanFull(),
                            
                        Forms\Components\DatePicker::make('fecha_emision')
                            ->required()
                            ->default(now()),
                            
                        Forms\Components\Select::make('estado')
                            ->options([
                                'PENDIENTE' => 'Pendiente',
                                'PAGADA' => 'Pagada',
                                'ANULADA' => 'Anulada',
                                'PARCIAL' => 'Parcial',
                            ])
                            ->default('PENDIENTE')
                            ->required(),
                            
                        Forms\Components\Textarea::make('observaciones')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Totales')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal (Suma de servicios)')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('L.')
                            ->readOnly()
                            ->helperText('Calculado automáticamente desde los servicios'),
                            
                        Forms\Components\TextInput::make('descuento_total')
                            ->label('Descuento del Paciente')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('L.')
                            ->readOnly()
                            ->helperText('Basado en el tipo de descuento del paciente'),
                            
                        Forms\Components\TextInput::make('impuesto_total')
                            ->label('Impuesto Total (ISV)')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('L.')
                            ->readOnly()
                            ->helperText('Calculado sobre servicios no exonerados'),
                            
                        Forms\Components\TextInput::make('total')
                            ->label('Total a Pagar')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('L.')
                            ->readOnly()
                            ->extraAttributes(['class' => 'font-bold text-lg'])
                            ->helperText('Subtotal + Impuestos - Descuentos'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('Servicios Incluidos')
                    ->schema([
                        Forms\Components\Placeholder::make('servicios_detalle')
                            ->label('')
                            ->content(function (?Factura $record): string {
                                $consultaId = request()->get('consulta_id') ?? $record?->consulta_id;
                                
                                if ($consultaId) {
                                    $servicios = \App\Models\FacturaDetalle::where('consulta_id', $consultaId)
                                        ->whereNull('factura_id')
                                        ->with('servicio')
                                        ->get();
                                    
                                    if ($servicios->count() > 0) {
                                        $contenido = "**Servicios a facturar:**\n\n";
                                        foreach ($servicios as $detalle) {
                                            $contenido .= "• {$detalle->servicio->nombre} - " .
                                                         "Cantidad: {$detalle->cantidad} - " .
                                                         "Total: L. " . number_format($detalle->total_linea, 2) . "\n";
                                        }
                                        return $contenido;
                                    }
                                }
                                
                                return 'No hay servicios para facturar';
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_factura')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                    
                TextColumn::make('paciente.persona.nombre_completo')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('medico.persona.nombre_completo')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('fecha_emision')
                    ->date()
                    ->sortable(),
                    
                TextColumn::make('total')
                    ->money('HNL')
                    ->sortable(),
                    
                TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PENDIENTE' => 'warning',
                        'PAGADA' => 'success',
                        'PARCIAL' => 'info',
                        'ANULADA' => 'danger',
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListFacturas::route('/'),
            'create' => Pages\CreateFacturas::route('/create'),
            'edit' => Pages\EditFacturas::route('/{record}/edit'),
            //'create-wizard' => Pages\CreateFacturaWizard::route('/create-wizard'),
        ];
    }
}
