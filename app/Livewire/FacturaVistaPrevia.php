<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\FacturaDiseno;

class FacturaVistaPrevia extends Component
{
    public $disenoId;
    public $datosFactura;
    
    // Propiedades del diseño que se actualizan en tiempo real
    public $color_primario = '#1e40af';
    public $color_secundario = '#64748b';
    public $color_acento = '#059669';
    public $color_texto = '#1f2937';
    
    public $fuente_titulo = 'Arial Black';
    public $fuente_texto = 'Arial';
    public $tamaño_titulo = 18;
    public $tamaño_texto = 12;
    public $tamaño_subtitulo = 14;
    
    public $mostrar_logo = true;
    public $posicion_logo = 'izquierda';
    public $tamaño_logo_ancho = 120;
    public $tamaño_logo_alto = 80;
    
    public $mostrar_titulo_factura = true;
    public $texto_titulo_factura = 'FACTURA';
    public $mostrar_numero_factura = true;
    public $mostrar_fecha_emision = true;
    
    public $mostrar_info_centro = true;
    public $mostrar_direccion_centro = true;
    public $mostrar_telefono_centro = true;
    public $mostrar_rtn_centro = true;
    
    public $mostrar_cai = true;
    public $posicion_cai = 'superior';
    public $mostrar_rango_cai = true;
    public $mostrar_fecha_limite_cai = true;
    
    public $mostrar_info_paciente = true;
    public $etiqueta_cliente = 'FACTURAR A:';
    public $mostrar_direccion_paciente = true;
    public $mostrar_telefono_paciente = true;
    public $mostrar_rtn_paciente = true;
    
    public $color_encabezado_tabla = '#f3f4f6';
    public $alternar_color_filas = true;
    public $color_fila_alterna = '#f9fafb';
    
    public $mostrar_subtotal = true;
    public $mostrar_descuentos = true;
    public $mostrar_impuestos = true;
    public $mostrar_total = true;
    public $posicion_totales = 'derecha';
    public $resaltar_total = true;
    
    public $mostrar_pie_pagina = true;
    public $texto_pie_pagina = 'Gracias por confiar en nuestros servicios médicos';
    public $mostrar_qr_pago = false;
    public $posicion_qr = 'derecha';
    
    public $css_personalizado = '';
    
        protected $listeners = ['actualizarVista', 'cargarDisenoId', 'recargarDatos'];
    
    public function recargarDatos()
    {
        $this->cargarDatosReales();
        $this->emit('vistaActualizada');
    }

    public function mount($disenoId = null)
    {
        $this->disenoId = $disenoId;
        
        // Obtener datos reales del sistema
        $this->cargarDatosReales();
        
        if ($disenoId) {
            $this->cargarDiseno($disenoId);
        }
    }
    
    private function cargarDatosReales()
    {
        // Si tenemos un diseño específico, intentar cargar datos de facturas que usen este diseño
        if ($this->disenoId) {
            $facturaEjemplo = $this->obtenerFacturaEjemplo();
            
            if ($facturaEjemplo) {
                $this->cargarDatosDeFactura($facturaEjemplo);
                return;
            }
        }
        
        // Si no hay factura específica, cargar datos genéricos del sistema
        $this->cargarDatosGenericos();
    }
    
    private function obtenerFacturaEjemplo()
    {
        // Buscar una factura que use este diseño
        $facturaConDiseno = \App\Models\Factura::where('factura_diseno_id', $this->disenoId)
            ->with([
                'paciente.persona',
                'medico.persona',
                'centro',
                'detalles.servicio',
                'caiCorrelativo.caiAutorizacion'
            ])
            ->latest()
            ->first();
        
        if ($facturaConDiseno) {
            return $facturaConDiseno;
        }
        
        // Si no hay facturas con este diseño, buscar la factura más reciente del centro
        $centroId = session('current_centro_id');
        return \App\Models\Factura::where('centro_id', $centroId)
            ->with([
                'paciente.persona',
                'medico.persona',
                'centro',
                'detalles.servicio',
                'caiCorrelativo.caiAutorizacion'
            ])
            ->latest()
            ->first();
    }
    
    private function cargarDatosDeFactura($factura)
    {
        $this->datosFactura = [
            // Información de la factura
            'numero_factura' => $factura->usa_cai && $factura->caiCorrelativo 
                ? $factura->caiCorrelativo->numero_factura 
                : $factura->generarNumeroSinCAI(),
            'fecha_emision' => $factura->fecha_emision->format('d/m/Y'),
            'fecha_vencimiento' => $factura->fecha_emision->addDays(30)->format('d/m/Y'),
            
            // Centro médico
            'centro' => [
                'nombre' => $factura->centro->nombre,
                'direccion' => $factura->centro->direccion,
                'telefono' => $factura->centro->telefono,
                'email' => $factura->centro->email,
                'rtn' => $factura->centro->rtn,
                'logo_url' => '/images/logo-clinica.png',
            ],
            
            // Médico
            'medico' => [
                'nombre' => $factura->medico->persona->primer_nombre . ' ' . $factura->medico->persona->primer_apellido,
                'especialidad' => $factura->medico->especialidades->first()->nombre ?? 'Medicina General',
                'numero_colegiacion' => $factura->medico->numero_colegiacion,
            ],
            
            // Paciente
            'paciente' => [
                'nombre' => $factura->paciente->persona->primer_nombre . ' ' . $factura->paciente->persona->primer_apellido,
                'direccion' => $factura->paciente->persona->direccion,
                'telefono' => $factura->paciente->persona->telefono,
                'rtn' => $factura->paciente->persona->identidad,
            ],
            
            // CAI
            'cai' => $factura->caiCorrelativo ? [
                'codigo' => $factura->caiCorrelativo->caiAutorizacion->codigo_cai,
                'rango_desde' => $factura->caiCorrelativo->caiAutorizacion->numero_desde,
                'rango_hasta' => $factura->caiCorrelativo->caiAutorizacion->numero_hasta,
                'fecha_limite' => $factura->caiCorrelativo->caiAutorizacion->fecha_limite_emision->format('d/m/Y'),
            ] : null,
            
            // Servicios/Productos
            'servicios' => $factura->detalles->map(function ($detalle) {
                return [
                    'cantidad' => $detalle->cantidad,
                    'descripcion' => $detalle->servicio->nombre,
                    'precio_unitario' => $detalle->precio_unitario,
                    'subtotal' => $detalle->subtotal,
                ];
            })->toArray(),
            
            // Totales
            'subtotal' => $factura->subtotal,
            'descuento_total' => $factura->descuento_total,
            'impuesto_total' => $factura->impuesto_total,
            'total' => $factura->total,
        ];
    }
    
    private function cargarDatosGenericos()
    {
        // Obtener el centro médico actual
        $centroId = session('current_centro_id');
        
        // Si no hay centro en sesión, usar el centro del usuario autenticado
        if (!$centroId && auth()->user()) {
            $centroId = auth()->user()->centro_id;
        }
        
        // Si aún no hay centro, usar el primer centro disponible
        if (!$centroId) {
            $centroId = \App\Models\Centros_Medico::first()?->id;
        }
        
        $centro = \App\Models\Centros_Medico::find($centroId);
        
        // Obtener el usuario actual y su médico asociado
        $usuario = auth()->user();
        $medico = null;
        $especialidad = 'Medicina General';
        
        if ($usuario) {
            // Buscar si el usuario actual es un médico usando persona_id
            $medico = null;
            if ($usuario->persona_id) {
                $medico = \App\Models\Medico::where('persona_id', $usuario->persona_id)
                    ->with(['persona', 'especialidades'])
                    ->first();
            }
            
            if (!$medico) {
                // Si no es médico, tomar el primer médico del centro
                $medico = \App\Models\Medico::where('centro_id', $centroId)
                    ->with(['persona', 'especialidades'])
                    ->first();
            }
            
            // Si aún no hay médico, tomar cualquier médico disponible
            if (!$medico) {
                $medico = \App\Models\Medico::with(['persona', 'especialidades'])->first();
            }
            
            if ($medico && $medico->especialidades->count() > 0) {
                $especialidad = $medico->especialidades->first()->nombre;
            }
        }
        
        // Si no hay médico, tomar el primer médico disponible como fallback
        if (!$medico) {
            $medico = \App\Models\Medico::with(['persona', 'especialidades'])->first();
            if ($medico && $medico->especialidades->count() > 0) {
                $especialidad = $medico->especialidades->first()->nombre;
            }
        }
        
        // Obtener el primer paciente para la vista previa
        $paciente = \App\Models\Pacientes::with('persona')->first();
        
        // Si no hay pacientes, crear datos de ejemplo
        if (!$paciente) {
            $pacienteData = [
                'nombre' => 'Paciente de Ejemplo',
                'identidad' => '0801-1985-12345',
                'telefono' => '(504) 000-0000',
                'direccion' => 'Dirección de ejemplo'
            ];
        } else {
            $pacienteData = [
                'nombre' => $paciente->persona->nombre_completo,
                'identidad' => $paciente->persona->dni ?? '0000-0000-00000',
                'telefono' => $paciente->persona->telefono ?? 'No disponible',
                'direccion' => $paciente->persona->direccion ?? 'Dirección no disponible'
            ];
        }
        
        // Obtener datos CAI más recientes del centro
        $caiAutorizacion = null;
        if ($centroId) {
            $caiAutorizacion = \App\Models\CAIAutorizaciones::where('centro_id', $centroId)
                ->where('estado', 'ACTIVA')
                ->orderBy('created_at', 'desc')
                ->first();
        }
        
        // Datos de ejemplo realistas con información real del sistema
        $this->datosFactura = [
            'numero_factura' => $this->generarNumeroFactura($caiAutorizacion),
            'fecha_emision' => now()->format('d/m/Y'),
            'centro' => [
                'nombre' => $centro ? $centro->nombre : 'Centro Médico',
                'direccion' => $centro ? $centro->direccion : 'Dirección no disponible',
                'telefono' => $centro ? "Tel: {$centro->telefono}" : 'Teléfono no disponible',
                'rtn' => $centro && $centro->rtn ? "RTN: {$centro->rtn}" : 'RTN: No disponible',
                'email' => $centro ? $centro->email : 'email@centromedico.com'
            ],
            'cai' => $caiAutorizacion ? [
                'numero' => $caiAutorizacion->cai,
                'rango_inicial' => $caiAutorizacion->rango_inicial,
                'rango_final' => $caiAutorizacion->rango_final,
                'fecha_limite' => $caiAutorizacion->fecha_limite ? 
                    $caiAutorizacion->fecha_limite->format('d/m/Y') : 
                    now()->addYear()->format('d/m/Y')
            ] : [
                'numero' => '4E2A5B1F-8C9D-4A3B-9E2F-1C8D7A5B9E3F',
                'rango_inicial' => '001-001-01-00000001',
                'rango_final' => '001-001-01-99999999',
                'fecha_limite' => now()->addYear()->format('d/m/Y')
            ],
            'paciente' => $pacienteData,
            'medico' => [
                'nombre' => $medico ? $medico->persona->nombre_completo : 'Dr. Médico de Ejemplo',
                'especialidad' => $especialidad
            ],
            'servicios' => [
                [
                    'descripcion' => 'Consulta Médica General',
                    'cantidad' => 1,
                    'precio_unitario' => 380.00,
                    'total' => 380.00
                ]
            ],
            'subtotal' => 380.00,
            'descuento_total' => 0.00,
            'impuesto_total' => 57.00,
            'total' => 437.00,
            'estado' => 'PENDIENTE',
            'historial_pagos' => [
                [
                    'fecha' => now()->subDays(1)->format('d/m/Y'),
                    'monto' => 200.00,
                    'estado' => 'Efectivo'
                ]
            ],
            'total_pagado' => 200.00,
            'saldo_pendiente' => 237.00
        ];
    }
    
    private function generarNumeroFactura($caiAutorizacion)
    {
        if ($caiAutorizacion) {
            // Obtener el último correlativo usado
            $ultimoCorrelativo = \App\Models\CAI_Correlativos::where('cai_autorizacion_id', $caiAutorizacion->id)
                ->orderBy('correlativo_actual', 'desc')
                ->first();
            
            if ($ultimoCorrelativo) {
                $siguienteNumero = $ultimoCorrelativo->correlativo_actual + 1;
                return str_pad($siguienteNumero, 11, '0', STR_PAD_LEFT);
            }
            
            return $caiAutorizacion->rango_inicial;
        }
        
        return '001-001-01-00000001';
    }
    
    public function cargarDiseno($disenoId)
    {
        $diseno = \App\Models\FacturaDiseno::find($disenoId);
        if ($diseno) {
            foreach ($diseno->toArray() as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }
    
    public function actualizarVistaPrevia($datos)
    {
        foreach ($datos as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function render()
    {
        // Obtener el diseño actual
        $diseno = null;
        if ($this->disenoId) {
            $diseno = \App\Models\FacturaDiseno::find($this->disenoId);
        }
        
        // Si no hay diseño específico, usar el predeterminado del centro
        if (!$diseno) {
            $centroId = session('current_centro_id');
            if (!$centroId && auth()->user()) {
                $centroId = auth()->user()->centro_id;
            }
            if (!$centroId) {
                $centroId = \App\Models\Centros_Medico::first()?->id;
            }
            
            if ($centroId) {
                $diseno = \App\Models\FacturaDiseno::where('centro_id', $centroId)
                    ->where('activo', true)
                    ->where('es_predeterminado', true)
                    ->first();
            }
        }
        
        // Si aún no hay diseño, crear uno básico para mostrar
        if (!$diseno) {
            $diseno = $this->crearDisenoBasico();
        }
        
        return view('livewire.factura-vista-previa', [
            'diseno' => $diseno
        ]);
    }
    
    private function crearDisenoBasico()
    {
        // Crear un objeto con valores predeterminados para evitar errores
        return (object) [
            'color_primario' => '#1e40af',
            'color_secundario' => '#64748b', 
            'color_acento' => '#059669',
            'color_texto' => '#1f2937',
            'fuente_titulo' => 'Arial Black',
            'fuente_texto' => 'Arial',
            'tamaño_titulo' => 18,
            'tamaño_texto' => 12,
            'tamaño_subtitulo' => 14,
            'mostrar_logo' => true,
            'logo_url' => null, // No hay logo por defecto
            'posicion_logo' => 'izquierda',
            'tamaño_logo_ancho' => 120,
            'tamaño_logo_alto' => 80,
            'mostrar_titulo_factura' => true,
            'texto_titulo_factura' => 'FACTURA',
            'mostrar_numero_factura' => true,
            'mostrar_fecha_emision' => true,
            'mostrar_fecha_vencimiento' => false,
            'mostrar_info_centro' => true,
            'mostrar_direccion_centro' => true,
            'mostrar_telefono_centro' => true,
            'mostrar_email_centro' => true,
            'mostrar_rtn_centro' => true,
            'mostrar_cai' => true,
            'mostrar_rango_cai' => true,
            'mostrar_fecha_limite_cai' => true,
            'mostrar_info_paciente' => true,
            'mostrar_direccion_paciente' => true,
            'mostrar_telefono_paciente' => true,
            'mostrar_rtn_paciente' => true,
            'mostrar_medico' => true,
            'mostrar_email' => true,
            // Colores compatibles con temas claro y oscuro
            'color_borde' => null, // Usar clases Tailwind automáticas
            'color_titulo' => null, // Usar clases Tailwind automáticas
            'color_texto_primario' => null, // Usar clases Tailwind automáticas
            'color_fondo_secundario' => null, // Usar clases Tailwind automáticas
            'color_fondo_tabla' => null, // Usar clases Tailwind automáticas
            'color_texto_secundario' => null, // Usar clases Tailwind automáticas
            'margenes' => ['top' => 20, 'right' => 15, 'bottom' => 20, 'left' => 15],
            'espaciado_lineas' => 5,
            'espaciado_secciones' => 15,
            'mostrar_tabla_servicios' => true,
            'mostrar_columna_cantidad' => true,
            'mostrar_columna_descripcion' => true,
            'mostrar_columna_precio_unitario' => true,
            'mostrar_columna_total' => true,
            'color_encabezado_tabla' => null, // Usar clases Tailwind automáticas
            'alternar_color_filas' => true,
            'color_fila_alterna' => null, // Usar clases Tailwind automáticas
            'mostrar_subtotal' => true,
            'mostrar_descuentos' => true,
            'mostrar_impuestos' => true,
            'mostrar_total' => true,
            'posicion_totales' => 'derecha',
            'resaltar_total' => true,
            'mostrar_pie_pagina' => true,
            'texto_pie_pagina' => 'Gracias por su preferencia',
            'mostrar_firma_medico' => false,
            'mostrar_sello_centro' => false,
            'mostrar_qr_pago' => false,
            'posicion_qr' => 'derecha',
            'mostrar_watermark' => false,
            'texto_watermark' => null,
            'color_watermark' => '#e5e7eb',
            'opacidad_watermark' => 10,
            'posicion_watermark' => 'centro',
            'mostrar_historial_pagos' => true,
            'mostrar_estado_factura' => true,
            'mostrar_saldo_pendiente' => true,
        ];
    }
}
