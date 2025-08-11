<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use App\Models\FacturaDiseno;
use App\Models\Centros_Medico;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Exception;

class DisenoFactura extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';
    protected static ?string $navigationLabel = 'Diseño de Facturas';
    protected static ?string $title = 'Configuración de Diseño de Facturas';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.diseno-factura';

    public ?array $data = [];

    protected $listeners = ['refresh-preview' => '$refresh'];

    public function mount(): void
    {
        $this->loadFacturaDisenoData();
        $this->form->fill($this->data);
    }

    public function updated($field)
    {
        // Dispatch los cambios al componente de vista previa
        $this->dispatch('actualizarVista', [
            'data' => $this->form->getState(),
            'field' => $field
        ]);
        $this->dispatch('refresh-preview');
    }

    public function getFormSchema(): array 
    {
        return $this->getFacturaDisenoFormSchema();
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->makeForm()
                ->schema($this->getFormSchema())
                ->statePath('data')
                ->live()
                ->debounce(500),
        ];
    }

    protected function loadFacturaDisenoData(): void
    {
        $user = Auth::user();
        $centroId = session('current_centro_id') ?? $user->centro_id;
        
        // Si no hay centro, usar el primer centro disponible
        if (!$centroId) {
            $centroId = Centros_Medico::first()?->id;
        }

        // Buscar diseño existente para el centro
        $diseno = FacturaDiseno::where('centro_id', $centroId)
            ->where('activo', true)
            ->first();

        if ($diseno) {
            $this->data = $diseno->toArray();
        } else {
            // Valores por defecto
            $this->data = [
                'centro_id' => $centroId,
                'nombre' => 'Diseño Principal',
                'descripcion' => 'Diseño principal de facturas para el centro médico',
                'activo' => true,
                'es_predeterminado' => true,
                
                // Colores
                'color_primario' => '#1e40af',
                'color_secundario' => '#64748b',
                'color_acento' => '#059669',
                'color_texto' => '#1f2937',
                'color_borde' => '#e5e7eb',
                'color_titulo' => '#1f2937',
                'color_texto_primario' => '#374151',
                'color_fondo_secundario' => '#f9fafb',
                
                // Tipografía
                'fuente_titulo' => 'Arial Black',
                'fuente_texto' => 'Arial',
                'tamaño_titulo' => 18,
                'tamaño_texto' => 12,
                'tamaño_subtitulo' => 14,
                
                // Logo
                'mostrar_logo' => true,
                'posicion_logo' => 'izquierda',
                'tamaño_logo_ancho' => 120,
                'tamaño_logo_alto' => 80,
                
                // Elementos de la factura
                'mostrar_titulo_factura' => true,
                'texto_titulo_factura' => 'FACTURA',
                'mostrar_numero_factura' => true,
                'mostrar_fecha_emision' => true,
                'mostrar_fecha_vencimiento' => false,
                
                // Información del centro
                'mostrar_info_centro' => true,
                'mostrar_direccion_centro' => true,
                'mostrar_telefono_centro' => true,
                'mostrar_email_centro' => true,
                'mostrar_rtn_centro' => true,
                
                // CAI
                'mostrar_cai' => true,
                'mostrar_rango_cai' => true,
                'mostrar_fecha_limite_cai' => true,
                
                // Información del paciente
                'mostrar_info_paciente' => true,
                'mostrar_direccion_paciente' => true,
                'mostrar_telefono_paciente' => true,
                'mostrar_rtn_paciente' => true,
                
                // Médico
                'mostrar_medico' => true,
                'mostrar_email' => true,
                
                // Tabla de servicios
                'mostrar_tabla_servicios' => true,
                'mostrar_columna_cantidad' => true,
                'mostrar_columna_descripcion' => true,
                'mostrar_columna_precio_unitario' => true,
                'mostrar_columna_total' => true,
                'color_encabezado_tabla' => '#f3f4f6',
                'alternar_color_filas' => true,
                'color_fila_alterna' => '#f9fafb',
                
                // Totales
                'mostrar_subtotal' => true,
                'mostrar_descuentos' => true,
                'mostrar_impuestos' => true,
                'mostrar_total' => true,
                'posicion_totales' => 'derecha',
                'resaltar_total' => true,
                
                // Pie de página
                'mostrar_pie_pagina' => true,
                'texto_pie_pagina' => 'Gracias por su preferencia',
                'mostrar_firma_medico' => false,
                'mostrar_sello_centro' => false,
                
                // Elementos adicionales
                'mostrar_qr_pago' => false,
                'posicion_qr' => 'derecha',
                'mostrar_watermark' => false,
                'texto_watermark' => null,
                'color_watermark' => '#e5e7eb',
                'opacidad_watermark' => 10,
                'posicion_watermark' => 'centro',
                
                // Estados de pago
                'mostrar_historial_pagos' => true,
                'mostrar_estado_factura' => true,
                'mostrar_saldo_pendiente' => true,
            ];
        }
    }

    protected function getFacturaDisenoFormSchema(): array
    {
        return [
            Section::make('Información Básica')
                ->description('Configuración básica del diseño')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('nombre')
                            ->label('Nombre del Diseño')
                            ->required()
                            ->maxLength(255)
                            ->default('Diseño Principal'),
                        
                        TextInput::make('descripcion')
                            ->label('Descripción')
                            ->maxLength(500)
                            ->default('Diseño principal de facturas para el centro médico'),
                    ]),
                    
                    Grid::make(2)->schema([
                        Toggle::make('activo')
                            ->label('Activo')
                            ->default(true)
                            ->helperText('Activar o desactivar este diseño'),
                        
                        Toggle::make('es_predeterminado')
                            ->label('Diseño Predeterminado')
                            ->default(true)
                            ->helperText('Usar como diseño por defecto'),
                    ]),
                ])
                ->collapsible(),

            Section::make('Colores del Diseño')
                ->description('Personaliza la paleta de colores de la factura')
                ->schema([
                    Grid::make(4)->schema([
                        ColorPicker::make('color_primario')
                            ->label('Color Primario')
                            ->default('#1e40af')
                            ->helperText('Color principal del encabezado'),
                        
                        ColorPicker::make('color_secundario')
                            ->label('Color Secundario')
                            ->default('#64748b')
                            ->helperText('Color para elementos secundarios'),
                        
                        ColorPicker::make('color_acento')
                            ->label('Color de Acento')
                            ->default('#059669')
                            ->helperText('Color para resaltar elementos'),
                        
                        ColorPicker::make('color_texto')
                            ->label('Color de Texto')
                            ->default('#1f2937')
                            ->helperText('Color del texto principal'),
                    ]),
                    
                    Grid::make(4)->schema([
                        ColorPicker::make('color_borde')
                            ->label('Color de Bordes')
                            ->default('#e5e7eb')
                            ->helperText('Color de los bordes de tabla'),
                        
                        ColorPicker::make('color_titulo')
                            ->label('Color de Títulos')
                            ->default('#1f2937')
                            ->helperText('Color específico para títulos'),
                        
                        ColorPicker::make('color_texto_primario')
                            ->label('Color Texto Primario')
                            ->default('#374151')
                            ->helperText('Color del texto en secciones importantes'),
                        
                        ColorPicker::make('color_fondo_secundario')
                            ->label('Color Fondo Secundario')
                            ->default('#f9fafb')
                            ->helperText('Color de fondo para secciones'),
                    ]),
                ])
                ->collapsible(),

            Section::make('Tipografía')
                ->description('Configuración de fuentes y tamaños de texto')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('fuente_titulo')
                            ->label('Fuente para Títulos')
                            ->options([
                                'Arial' => 'Arial',
                                'Arial Black' => 'Arial Black',
                                'Helvetica' => 'Helvetica',
                                'Times New Roman' => 'Times New Roman',
                                'Georgia' => 'Georgia',
                                'Verdana' => 'Verdana',
                                'Tahoma' => 'Tahoma',
                            ])
                            ->default('Arial Black')
                            ->searchable(),
                        
                        Select::make('fuente_texto')
                            ->label('Fuente para Texto')
                            ->options([
                                'Arial' => 'Arial',
                                'Arial Black' => 'Arial Black',
                                'Helvetica' => 'Helvetica',
                                'Times New Roman' => 'Times New Roman',
                                'Georgia' => 'Georgia',
                                'Verdana' => 'Verdana',
                                'Tahoma' => 'Tahoma',
                            ])
                            ->default('Arial')
                            ->searchable(),
                    ]),
                    
                    Grid::make(3)->schema([
                        TextInput::make('tamaño_titulo')
                            ->label('Tamaño Título (px)')
                            ->numeric()
                            ->default(18)
                            ->minValue(10)
                            ->maxValue(30)
                            ->suffix('px'),
                        
                        TextInput::make('tamaño_subtitulo')
                            ->label('Tamaño Subtítulo (px)')
                            ->numeric()
                            ->default(14)
                            ->minValue(8)
                            ->maxValue(24)
                            ->suffix('px'),
                        
                        TextInput::make('tamaño_texto')
                            ->label('Tamaño Texto (px)')
                            ->numeric()
                            ->default(12)
                            ->minValue(8)
                            ->maxValue(20)
                            ->suffix('px'),
                    ]),
                ])
                ->collapsible(),

            Section::make('Logo y Encabezado')
                ->description('Configuración del logo y elementos del encabezado')
                ->schema([
                    Grid::make(2)->schema([
                        Toggle::make('mostrar_logo')
                            ->label('Mostrar Logo')
                            ->default(true)
                            ->reactive(),
                        
                        Select::make('posicion_logo')
                            ->label('Posición del Logo')
                            ->options([
                                'izquierda' => 'Izquierda',
                                'centro' => 'Centro',
                                'derecha' => 'Derecha',
                            ])
                            ->default('izquierda')
                            ->visible(fn ($get) => $get('mostrar_logo')),
                    ]),
                    
                    Grid::make(2)->schema([
                        TextInput::make('tamaño_logo_ancho')
                            ->label('Ancho del Logo (px)')
                            ->numeric()
                            ->default(120)
                            ->minValue(50)
                            ->maxValue(300)
                            ->suffix('px')
                            ->visible(fn ($get) => $get('mostrar_logo')),
                        
                        TextInput::make('tamaño_logo_alto')
                            ->label('Alto del Logo (px)')
                            ->numeric()
                            ->default(80)
                            ->minValue(30)
                            ->maxValue(200)
                            ->suffix('px')
                            ->visible(fn ($get) => $get('mostrar_logo')),
                    ]),
                ])
                ->collapsible(),

            Section::make('Elementos de la Factura')
                ->description('Selecciona qué elementos mostrar en la factura')
                ->schema([
                    Grid::make(3)->schema([
                        Toggle::make('mostrar_titulo_factura')
                            ->label('Mostrar Título "FACTURA"')
                            ->default(true)
                            ->reactive(),
                        
                        Toggle::make('mostrar_numero_factura')
                            ->label('Mostrar Número de Factura')
                            ->default(true),
                        
                        Toggle::make('mostrar_fecha_emision')
                            ->label('Mostrar Fecha de Emisión')
                            ->default(true),
                    ]),
                    
                    TextInput::make('texto_titulo_factura')
                        ->label('Texto del Título')
                        ->default('FACTURA')
                        ->visible(fn ($get) => $get('mostrar_titulo_factura')),
                    
                    Grid::make(2)->schema([
                        Toggle::make('mostrar_fecha_vencimiento')
                            ->label('Mostrar Fecha de Vencimiento')
                            ->default(false),
                        
                        Toggle::make('mostrar_cai')
                            ->label('Mostrar Información CAI')
                            ->default(true),
                    ]),
                ])
                ->collapsible(),

            Section::make('Información del Centro Médico')
                ->description('Configurar qué información del centro mostrar')
                ->schema([
                    Grid::make(3)->schema([
                        Toggle::make('mostrar_info_centro')
                            ->label('Mostrar Info del Centro')
                            ->default(true)
                            ->reactive(),
                        
                        Toggle::make('mostrar_direccion_centro')
                            ->label('Mostrar Dirección')
                            ->default(true)
                            ->visible(fn ($get) => $get('mostrar_info_centro')),
                        
                        Toggle::make('mostrar_telefono_centro')
                            ->label('Mostrar Teléfono')
                            ->default(true)
                            ->visible(fn ($get) => $get('mostrar_info_centro')),
                    ]),
                    
                    
                ])
                ->collapsible(),

            Section::make('Información del Paciente')
                ->description('Configurar qué información del paciente mostrar')
                ->schema([
                    Grid::make(3)->schema([
                        Toggle::make('mostrar_info_paciente')
                            ->label('Mostrar Info del Paciente')
                            ->default(true)
                            ->reactive(),
                        
                        Toggle::make('mostrar_direccion_paciente')
                            ->label('Mostrar Dirección')
                            ->default(true)
                            ->visible(fn ($get) => $get('mostrar_info_paciente')),
                        
                        Toggle::make('mostrar_telefono_paciente')
                            ->label('Mostrar Teléfono')
                            ->default(true)
                            ->visible(fn ($get) => $get('mostrar_info_paciente')),
                    ]),
                    
                    Grid::make(2)->schema([
                        Toggle::make('mostrar_rtn_paciente')
                            ->label('Mostrar RTN del Paciente')
                            ->default(true)
                            ->visible(fn ($get) => $get('mostrar_info_paciente')),
                        
                        Toggle::make('mostrar_medico')
                            ->label('Mostrar Información del Médico')
                            ->default(true),
                    ]),
                ])
                ->collapsible(),

            Section::make('Tabla de Servicios')
                ->description('Configuración de la tabla de servicios/productos')
                ->schema([
                    Toggle::make('mostrar_tabla_servicios')
                        ->label('Mostrar Tabla de Servicios')
                        ->default(true)
                        ->reactive(),
                    
                    Grid::make(4)->schema([
                        Toggle::make('mostrar_columna_cantidad')
                            ->label('Columna Cantidad')
                            ->default(true)
                            ->visible(fn ($get) => $get('mostrar_tabla_servicios')),
                        
                        Toggle::make('mostrar_columna_descripcion')
                            ->label('Columna Descripción')
                            ->default(true)
                            ->visible(fn ($get) => $get('mostrar_tabla_servicios')),
                        
                        Toggle::make('mostrar_columna_precio_unitario')
                            ->label('Columna Precio Unitario')
                            ->default(true)
                            ->visible(fn ($get) => $get('mostrar_tabla_servicios')),
                        
                        Toggle::make('mostrar_columna_total')
                            ->label('Columna Total')
                            ->default(true)
                            ->visible(fn ($get) => $get('mostrar_tabla_servicios')),
                    ]),
                    
                    Grid::make(3)->schema([
                        ColorPicker::make('color_encabezado_tabla')
                            ->label('Color Encabezado Tabla')
                            ->default('#f3f4f6')
                            ->visible(fn ($get) => $get('mostrar_tabla_servicios')),
                        
                        Toggle::make('alternar_color_filas')
                            ->label('Alternar Color de Filas')
                            ->default(true)
                            ->visible(fn ($get) => $get('mostrar_tabla_servicios'))
                            ->reactive(),
                        
                        ColorPicker::make('color_fila_alterna')
                            ->label('Color Fila Alterna')
                            ->default('#f9fafb')
                            ->visible(fn ($get) => $get('mostrar_tabla_servicios') && $get('alternar_color_filas')),
                    ]),
                ])
                ->collapsible(),

            Section::make('Totales y Cálculos')
                ->description('Configuración de la sección de totales')
                ->schema([
                    Grid::make(4)->schema([
                        Toggle::make('mostrar_subtotal')
                            ->label('Mostrar Subtotal')
                            ->default(true),
                        
                        Toggle::make('mostrar_descuentos')
                            ->label('Mostrar Descuentos')
                            ->default(true),
                        
                        Toggle::make('mostrar_impuestos')
                            ->label('Mostrar Impuestos')
                            ->default(true),
                        
                        Toggle::make('mostrar_total')
                            ->label('Mostrar Total')
                            ->default(true),
                    ]),
                    
                    Grid::make(2)->schema([
                        Select::make('posicion_totales')
                            ->label('Posición de Totales')
                            ->options([
                                'derecha' => 'Derecha',
                                'izquierda' => 'Izquierda',
                                'centro' => 'Centro',
                            ])
                            ->default('derecha'),
                        
                        Toggle::make('resaltar_total')
                            ->label('Resaltar Total Final')
                            ->default(true)
                            ->helperText('Usar formato especial para el total'),
                    ]),
                ])
                ->collapsible(),

            Section::make('Pie de Página')
                ->description('Configuración del pie de página de la factura')
                ->schema([
                    Toggle::make('mostrar_pie_pagina')
                        ->label('Mostrar Pie de Página')
                        ->default(true)
                        ->reactive(),
                    
                    Textarea::make('texto_pie_pagina')
                        ->label('Texto del Pie de Página')
                        ->default('Gracias por su preferencia')
                        ->rows(2)
                        ->visible(fn ($get) => $get('mostrar_pie_pagina')),
                    
                    Grid::make(2)->schema([
                        Toggle::make('mostrar_firma_medico')
                            ->label('Mostrar Firma del Médico')
                            ->default(false),
                        
                        Toggle::make('mostrar_sello_centro')
                            ->label('Mostrar Sello del Centro')
                            ->default(false),
                    ]),
                ])
                ->collapsible(),


            Section::make('Estados y Pagos')
                ->description('Configuración para estados de factura y historial de pagos')
                ->schema([
                    Grid::make(3)->schema([
                        Toggle::make('mostrar_historial_pagos')
                            ->label('Mostrar Historial de Pagos')
                            ->default(true),
                        
                        Toggle::make('mostrar_estado_factura')
                            ->label('Mostrar Estado de Factura')
                            ->default(true),
                        
                        Toggle::make('mostrar_saldo_pendiente')
                            ->label('Mostrar Saldo Pendiente')
                            ->default(true),
                    ]),
                ])
                ->collapsible(),
        ];
    }

    public function guardar(): void
    {
        try {
            $data = $this->form->getState();
            
            $centroId = session('current_centro_id') ?? Auth::user()->centro_id;
            if (!$centroId) {
                $centroId = Centros_Medico::first()?->id;
            }
            
            $data['centro_id'] = $centroId;
            
            // Buscar diseño existente
            $diseno = FacturaDiseno::where('centro_id', $centroId)
                ->where('activo', true)
                ->first();
            
            if ($diseno) {
                $diseno->update($data);
            } else {
                FacturaDiseno::create($data);
            }
            
            Notification::make()
                ->title('Diseño guardado exitosamente')
                ->success()
                ->send();
                
        } catch (Exception $e) {
            Notification::make()
                ->title('Error al guardar el diseño')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function restaurarDefecto(): void
    {
        try {
            $this->loadFacturaDisenoData();
            $this->form->fill($this->data);
            
            Notification::make()
                ->title('Diseño restaurado a valores por defecto')
                ->success()
                ->send();
                
        } catch (Exception $e) {
            Notification::make()
                ->title('Error al restaurar diseño')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('vista_previa')
                ->label('Vista Previa Completa')
                ->url('/facturas/preview-demo')
                ->color('info')
                ->icon('heroicon-o-eye')
                ->openUrlInNewTab(),
            
            \Filament\Actions\Action::make('guardar')
                ->label('Guardar Cambios')
                ->action('guardar')
                ->color('success')
                ->icon('heroicon-o-check'),
            
            \Filament\Actions\Action::make('restaurar')
                ->label('Restaurar por Defecto')
                ->action('restaurarDefecto')
                ->color('gray')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalHeading('Restaurar diseño por defecto')
                ->modalDescription('¿Estás seguro de que quieres restaurar el diseño a los valores por defecto? Se perderán todos los cambios no guardados.')
                ->modalSubmitActionLabel('Sí, restaurar'),
        ];
    }
}
