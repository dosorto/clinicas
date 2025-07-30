<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Notifications\Notification;
use App\Models\FacturaConfiguracion;

class ConfiguracionFactura extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Configuración de Factura';
    protected static ?string $title = 'Configuración de Factura';
    protected static ?string $navigationGroup = 'Facturación';
    protected static string $view = 'filament.pages.configuracion-factura';

    public ?array $data = [];

    public function mount(): void
    {
        $config = FacturaConfiguracion::first();
        $this->form->fill($config ? $config->toArray() : []);
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Configuración de Factura')
                ->schema([
                    Forms\Components\FileUpload::make('logo')
                        ->label('Logo de la Empresa')
                        ->disk('public')
                        ->directory('facturas/logos')
                        ->image()
                        ->imageEditor()
                        ->maxSize(2048)
                        ->helperText('Logo que aparecerá en la factura (máx 2MB)')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('razon_social')
                        ->label('Razón Social o Nombre Comercial')
                        ->required()
                        ->live(),

                    Forms\Components\TextInput::make('telefono')
                        ->label('Teléfonos')
                        ->helperText('Separados por coma si son varios')
                        ->required()
                        ->live(),

                    Forms\Components\TextInput::make('direccion')
                        ->label('Dirección')
                        ->required()
                        ->live(),

                    Forms\Components\ColorPicker::make('color_primario')
                        ->label('Color Primario')
                        ->default('#2563eb'),

                    Forms\Components\ColorPicker::make('color_secundario')
                        ->label('Color Secundario')
                        ->default('#64748b'),

                    Forms\Components\Textarea::make('encabezado')
                        ->label('Texto de Encabezado')
                        ->rows(2),

                    Forms\Components\Textarea::make('pie_pagina')
                        ->label('Texto de Pie de Página')
                        ->rows(2),

                    Forms\Components\TextInput::make('formato_numeracion')
                        ->label('Formato de Numeración')
                        ->placeholder('Ej: FAC-00001'),
                ]),
            Forms\Components\Section::make('Vista Previa de Factura')
                ->description('Visualización en tiempo real de la factura')
                ->schema([
                    Forms\Components\Placeholder::make('preview')
                        ->label('')
                        ->dehydrated(false)
                        ->content(function (callable $get) {
                            $config = (object) $get();
                            // Defaults visuales SOLO si el campo no existe en el formulario
                            $defaults = [
                                'color_primario' => '#2563eb',
                                'color_secundario' => '#64748b',
                                'formato_numeracion' => 'FAC-00001',
                                'logo' => null,
                                'razon_social' => 'Razón Social',
                                'telefono' => '',
                                'direccion' => 'Dirección de la Empresa',
                                'encabezado' => '',
                                'pie_pagina' => '',
                            ];
                            foreach ($defaults as $key => $value) {
                                if (!isset($config->$key) || $config->$key === '') {
                                    $config->$key = $value;
                                }
                            }

                            // Simular datos de factura para la vista previa
                            $user = auth()->user();
                            // Datos del médico logueado
                            $medico = $user && $user->medico ? $user->medico : (object)[
                                'persona' => (object)[
                                    'primer_nombre' => $user->name ?? 'Juan',
                                    'primer_apellido' => '',
                                    'telefono' => $user->telefono ?? '9999-9999',
                                ],
                                'numero_colegiacion' => $user->medico->numero_colegiacion ?? '12345',
                            ];
                            // Datos del centro
                            $centro = $user && $user->centro ? $user->centro : (object)[
                                'nombre_centro' => 'Centro Médico Demo',
                                'direccion' => 'Dirección Demo',
                                'telefono' => '8888-8888',
                            ];
                            // Simular paciente y servicio
                            $paciente = (object)[
                                'nombre_completo' => 'Paciente Ejemplo',
                                'telefono' => '7777-7777',
                            ];
                            $servicio = (object) [
                                'id' => 1,
                                'nombre' => 'Consulta General',
                                'codigo' => 'CG-001',
                            ];
                            $descuento = (object) [
                                'id' => 1,
                                'nombre' => 'Descuento Promoción',
                                'monto' => 50.00,
                            ];
                            $impuesto = (object) [
                                'id' => 1,
                                'nombre' => 'ISV',
                                'porcentaje' => 15,
                                'monto' => 67.5,
                            ];
                            $precio = 500.00;
                            $detalle = (object) [
                                'servicio' => $servicio,
                                'descripcion' => $servicio->nombre,
                                'cantidad' => 1,
                                'precio_unitario' => $precio,
                                'subtotal' => $precio,
                                'descuento' => $descuento,
                                'descuento_monto' => 50.00,
                                'impuesto' => $impuesto,
                                'impuesto_monto' => 67.5,
                                'total_linea' => $precio - 50.00 + 67.5,
                                'total' => $precio - 50.00 + 67.5,
                            ];
                            // Agregar varias filas de ejemplo para la tabla de detalles
                            $detalle2 = clone $detalle;
                            $detalle2->servicio = (object) [
                                'id' => 2,
                                'nombre' => 'Rayos X',
                                'codigo' => 'RX-002',
                            ];
                            $detalle2->descripcion = 'Rayos X';
                            $detalle2->cantidad = 2;
                            $detalle2->precio_unitario = 300.00;
                            $detalle2->subtotal = 600.00;
                            $detalle2->descuento_monto = 0.00;
                            $detalle2->impuesto_monto = 90.00;
                            $detalle2->total_linea = 690.00;
                            $detalle2->total = 690.00;

                            $detalles = [$detalle, $detalle2];
                            $pagos = [
                                (object) [
                                    'monto' => 517.5,
                                    'tipo_pago' => (object) ['nombre' => 'Efectivo'],
                                    'fecha' => now(),
                                ]
                            ];
                            $caiCorrelativo = (object) [
                                'numero_factura' => 'FAC-00001',
                                'cai' => 'A1B2C3D4E5',
                                'fecha_limite' => now()->addMonths(1),
                            ];
                            $factura = (object) [
                                'fecha_emision' => now(),
                                'numero_factura' => $config->formato_numeracion,
                                'subtotal' => $precio,
                                'descuento_total' => 50.00,
                                'impuesto_total' => 67.5,
                                'total' => $precio - 50.00 + 67.5,
                                'medico' => $medico,
                                'centro' => $centro,
                                'paciente' => $paciente,
                                'detalles' => $detalles,
                                'pagos' => $pagos,
                                'caiCorrelativo' => $caiCorrelativo,
                            ];
                            // Forzar color de texto oscuro solo en el contenido principal, pero permitir color primario en info de médico/paciente
                            $html = view('factura.imprimir', ['factura' => $factura, 'config' => $config])->render();
                            // Solo forzar color en el body si no es la sección de info destacada
                            $html = preg_replace_callback(
                                '/(<body[^>]*>)([\s\S]*?)(<\/body>)/i',
                                function ($matches) {
                                    // Solo forzar color en el contenido principal, no en los spans/clases de info
                                    $bodyOpen = $matches[1];
                                    $bodyContent = $matches[2];
                                    $bodyClose = $matches[3];
                                    // Quitar color forzado en spans con clase .info-medico, .info-paciente
                                    $bodyContent = preg_replace('/(<span[^>]*class=["\']info-(medico|paciente)["\'][^>]*)(style=["\'][^"\']*["\'])?([^>]*>)/i', '$1 style="color:inherit;"$4', $bodyContent);
                                    // Forzar color oscuro en el resto
                                    return $bodyOpen . '<div style="color:#222 !important;">' . $bodyContent . '</div>' . $bodyClose;
                                },
                                $html
                            );
                            return new \Illuminate\Support\HtmlString($html);
                        }),
                ])
                ->collapsible()
                ->collapsed(false),
        ];
    }

    public function save()
    {
        $data = $this->form->getState();

        $config = FacturaConfiguracion::first() ?? new FacturaConfiguracion();
        $config->fill($data);
        $config->save();

        Notification::make()
            ->title('Configuración guardada')
            ->success()
            ->send();
    }
}
