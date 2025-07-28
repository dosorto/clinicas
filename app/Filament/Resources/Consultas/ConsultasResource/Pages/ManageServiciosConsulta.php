<?php

namespace App\Filament\Resources\Consultas\ConsultasResource\Pages;

use App\Filament\Resources\Consultas\ConsultasResource;
use App\Models\Consulta;
use App\Models\Servicio;
use App\Models\FacturaDetalle;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Notifications\Notification;
use App\Filament\Resources\Facturas\FacturasResource;

class ManageServiciosConsulta extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = ConsultasResource::class;
    protected static string $view = 'filament.resources.consultas.pages.manage-servicios-consulta';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('agregar_servicios')
                ->label('Agregar Servicios')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->form([
                    Repeater::make('servicios_data')
                        ->label('Servicios a Agregar')
                        ->schema([
                            Forms\Components\Select::make('servicio_id')
                                ->label('Servicio')
                                ->options(function () {
                                    return Servicio::all()->mapWithKeys(function ($servicio) {
                                        return [$servicio->id => $servicio->nombre . ' - L.' . number_format($servicio->precio_unitario, 2)];
                                    });
                                })
                                ->searchable()
                                ->preload()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    if ($state) {
                                        $servicio = Servicio::find($state);
                                        if ($servicio) {
                                            $cantidad = (int) ($get('cantidad') ?? 1);
                                            $total = $servicio->precio_unitario * $cantidad;
                                            $set('total_linea', number_format($total, 2, '.', ''));
                                        }
                                    }
                                    
                                    // Verificar duplicados
                                    $serviciosData = $get('../../servicios_data') ?? [];
                                    $serviciosSeleccionados = array_filter(
                                        array_column($serviciosData, 'servicio_id'),
                                        fn($id) => !is_null($id)
                                    );
                                    
                                    $repetidos = array_count_values($serviciosSeleccionados);
                                    if (isset($repetidos[$state]) && $repetidos[$state] > 1) {
                                        Notification::make()
                                            ->title('Servicio duplicado')
                                            ->body('No puede seleccionar el mismo servicio más de una vez.')
                                            ->danger()
                                            ->send();
                                        $set('servicio_id', null);
                                        $set('total_linea', 0);
                                    }
                                }),
                                
                            Forms\Components\TextInput::make('cantidad')
                                ->label('Cantidad')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                    $servicioId = $get('servicio_id');
                                    if ($servicioId) {
                                        $servicio = Servicio::find($servicioId);
                                        if ($servicio) {
                                            $cantidad = (int) ($state ?? 1);
                                            $total = $servicio->precio_unitario * $cantidad;
                                            $set('total_linea', number_format($total, 2, '.', ''));
                                        }
                                    }
                                }),
                                
                            Forms\Components\TextInput::make('total_linea')
                                ->label('Total')
                                ->numeric()
                                ->prefix('L.')
                                ->step(0.01)
                                ->disabled()
                                ->dehydrated(true)
                                ->helperText('Cantidad × Precio del Servicio'),
                        ])
                        ->columns(3)
                        ->defaultItems(1)
                        ->addActionLabel('Agregar Otro Servicio')
                        ->itemLabel(fn (array $state): ?string => 
                            isset($state['servicio_id']) ? 
                            (Servicio::find($state['servicio_id'])?->nombre ?? 'Nuevo Servicio') : 
                            'Nuevo Servicio'
                        )
                        ->collapsible()
                        ->cloneable()
                        ->reorderable()
                        ->deleteAction(
                            fn (Forms\Components\Actions\Action $action) => $action
                                ->requiresConfirmation()
                                ->modalDescription('¿Estás seguro de que deseas eliminar este servicio?')
                        )
                        ->minItems(1)
                        ->columnSpanFull()
                ])
                ->action(function (array $data): void {
                    $serviciosData = $data['servicios_data'] ?? [];
                    
                    $serviciosCreados = 0;
                    
                    foreach ($serviciosData as $servicioData) {
                        if (!empty($servicioData['servicio_id'])) {
                            $servicio = Servicio::find($servicioData['servicio_id']);
                            if ($servicio) {
                                $cantidad = (int) ($servicioData['cantidad'] ?? 1);
                                $total_linea = $servicio->precio_unitario * $cantidad;
                                
                                FacturaDetalle::create([
                                    'consulta_id' => $this->record->id,
                                    'servicio_id' => $servicioData['servicio_id'],
                                    'cantidad' => $cantidad,
                                    'subtotal' => $total_linea,
                                    'total_linea' => $total_linea,
                                    'descuento_monto' => 0,
                                    'impuesto_monto' => 0,
                                ]);
                                
                                $serviciosCreados++;
                            }
                        }
                    }
                    
                    Notification::make()
                        ->title('Servicios agregados exitosamente')
                        ->body("{$serviciosCreados} servicio(s) agregado(s) a la consulta.")
                        ->success()
                        ->send();
                        
                    // QUITAR completamente la redirección
                    // Filament automáticamente actualiza la tabla
                })
                ->modalHeading('Agregar Servicios a la Consulta')
                ->modalSubmitActionLabel('Agregar Servicios')
                ->modalWidth('5xl'),
                
            Actions\Action::make('crear_factura')
                ->label('Continuar a Facturación')
                ->icon('heroicon-o-arrow-right')
                ->color('primary')
                ->url(function () {
                    $subtotal = $this->getServiciosTotal();
                    return FacturasResource::getUrl('create', [
                        'consulta_id' => $this->record->id,
                        'subtotal' => $subtotal
                    ]);
                })
                ->visible(function () {
                    return FacturaDetalle::where('consulta_id', $this->record->id)
                        ->whereNull('factura_id')
                        ->exists();
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                FacturaDetalle::query()
                    ->where('consulta_id', $this->record->id)
                    ->whereNull('factura_id')
                    ->with('servicio')
            )
            ->columns([
                Tables\Columns\TextColumn::make('servicio.nombre')
                    ->label('Servicio')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                    
                Tables\Columns\TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->alignCenter()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('servicio.precio_unitario')
                    ->label('Precio Unit.')
                    ->money('HNL')
                    ->alignEnd()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('total_linea')
                    ->label('Total')
                    ->money('HNL')
                    ->alignEnd()
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\Select::make('servicio_id')
                            ->label('Servicio')
                            ->options(function () {
                                return Servicio::all()->mapWithKeys(function ($servicio) {
                                    return [$servicio->id => $servicio->nombre . ' - L.' . number_format($servicio->precio_unitario, 2)];
                                });
                            })
                            ->searchable()
                            ->required(),
                            
                        Forms\Components\TextInput::make('cantidad')
                            ->label('Cantidad')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $servicio = Servicio::find($data['servicio_id']);
                        if ($servicio) {
                            $cantidad = (int) ($data['cantidad'] ?? 1);
                            $total = $servicio->precio_unitario * $cantidad;
                            
                            $data['subtotal'] = $total;
                            $data['total_linea'] = $total;
                            $data['descuento_monto'] = 0;
                            $data['impuesto_monto'] = 0;
                        }
                        
                        return $data;
                    })
                    ->after(function () {
                        Notification::make()
                            ->title('Servicio actualizado')
                            ->body('El servicio se ha actualizado correctamente.')
                            ->success()
                            ->send();
                    }),
                    
                Tables\Actions\DeleteAction::make()
                    ->after(function () {
                        Notification::make()
                            ->title('Servicio eliminado')
                            ->body('El subtotal se ha actualizado automáticamente')
                            ->warning()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function () {
                            Notification::make()
                                ->title('Servicios eliminados')
                                ->body('Los servicios han sido eliminados correctamente')
                                ->warning()
                                ->send();
                        }),
                ]),
            ])
            ->emptyStateHeading('No hay servicios agregados')
            ->emptyStateDescription('Agrega servicios a esta consulta usando el botón "Agregar Servicios"')
            ->emptyStateIcon('heroicon-o-plus-circle');
    }

    public function getServiciosTotal(): float
    {
        return FacturaDetalle::where('consulta_id', $this->record->id)
            ->whereNull('factura_id')
            ->sum('total_linea');
    }

    public function getCantidadServicios(): int
    {
        return FacturaDetalle::where('consulta_id', $this->record->id)
            ->whereNull('factura_id')
            ->count();
    }
}