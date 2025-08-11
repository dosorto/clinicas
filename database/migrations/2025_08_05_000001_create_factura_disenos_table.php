<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('factura_disenos', function (Blueprint $table) {
            $table->id();
            
            // Información básica del diseño
            $table->string('nombre')->unique(); // Nombre del diseño/plantilla
            $table->text('descripcion')->nullable(); // Descripción del diseño
            $table->boolean('es_predeterminado')->default(false); // Si es el diseño por defecto
            $table->boolean('activo')->default(true); // Si está activo o no
            
            // Configuración de plantilla
            $table->string('template_archivo')->default('factura_basica'); // Archivo de plantilla blade
            $table->enum('orientacion_papel', ['portrait', 'landscape'])->default('portrait');
            $table->enum('tamaño_papel', ['A4', 'Letter', 'Legal'])->default('A4');
            
            // Colores del diseño
            $table->string('color_primario', 7)->default('#1e40af'); // Color principal (#HEX)
            $table->string('color_secundario', 7)->default('#64748b'); // Color secundario
            $table->string('color_acento', 7)->default('#059669'); // Color de acento
            $table->string('color_texto', 7)->default('#1f2937'); // Color del texto principal
            
            // Configuración de tipografía
            $table->string('fuente_titulo')->default('Arial Black'); // Fuente para títulos
            $table->string('fuente_texto')->default('Arial'); // Fuente para texto normal
            $table->integer('tamaño_titulo')->default(18); // Tamaño fuente títulos
            $table->integer('tamaño_texto')->default(12); // Tamaño fuente normal
            $table->integer('tamaño_subtitulo')->default(14); // Tamaño fuente subtítulos
            
            // Márgenes y espaciado (en milímetros)
            $table->json('margenes')->nullable(); // {top: 20, right: 15, bottom: 20, left: 15}
            $table->integer('espaciado_lineas')->default(5); // Espaciado entre líneas
            $table->integer('espaciado_secciones')->default(15); // Espaciado entre secciones
            
            // Elementos visuales
            $table->boolean('mostrar_logo')->default(true); // Mostrar logo del centro
            $table->string('posicion_logo')->default('izquierda'); // izquierda, centro, derecha
            $table->integer('tamaño_logo_ancho')->default(120); // Ancho del logo en px
            $table->integer('tamaño_logo_alto')->default(80); // Alto del logo en px
            
            // Encabezado
            $table->boolean('mostrar_titulo_factura')->default(true);
            $table->string('texto_titulo_factura')->default('FACTURA');
            $table->boolean('mostrar_numero_factura')->default(true);
            $table->boolean('mostrar_fecha_emision')->default(true);
            $table->boolean('mostrar_fecha_vencimiento')->default(false);
            
            // Información del centro médico
            $table->boolean('mostrar_info_centro')->default(true);
            $table->boolean('mostrar_direccion_centro')->default(true);
            $table->boolean('mostrar_telefono_centro')->default(true);
            $table->boolean('mostrar_email_centro')->default(true);
            $table->boolean('mostrar_rtn_centro')->default(true);
            
            // Información CAI
            $table->boolean('mostrar_cai')->default(true);
            $table->boolean('mostrar_rango_cai')->default(true);
            $table->boolean('mostrar_fecha_limite_cai')->default(true);
            $table->string('posicion_cai')->default('superior'); // superior, inferior, lateral
            
            // Información del paciente/cliente
            $table->boolean('mostrar_info_paciente')->default(true);
            $table->boolean('mostrar_direccion_paciente')->default(true);
            $table->boolean('mostrar_telefono_paciente')->default(true);
            $table->boolean('mostrar_rtn_paciente')->default(true);
            $table->string('etiqueta_cliente')->default('Facturar a:'); // Texto personalizable
            
            // Tabla de servicios/productos
            $table->boolean('mostrar_tabla_servicios')->default(true);
            $table->boolean('mostrar_columna_cantidad')->default(true);
            $table->boolean('mostrar_columna_descripcion')->default(true);
            $table->boolean('mostrar_columna_precio_unitario')->default(true);
            $table->boolean('mostrar_columna_total')->default(true);
            $table->string('color_encabezado_tabla', 7)->default('#f3f4f6');
            $table->boolean('alternar_color_filas')->default(true);
            $table->string('color_fila_alterna', 7)->default('#f9fafb');
            
            // Totales y cálculos
            $table->boolean('mostrar_subtotal')->default(true);
            $table->boolean('mostrar_descuentos')->default(true);
            $table->boolean('mostrar_impuestos')->default(true);
            $table->boolean('mostrar_total')->default(true);
            $table->string('posicion_totales')->default('derecha'); // derecha, centro, izquierda
            $table->boolean('resaltar_total')->default(true);
            
            // Pie de página
            $table->boolean('mostrar_pie_pagina')->default(true);
            $table->text('texto_pie_pagina')->nullable(); // Texto personalizable del pie
            $table->boolean('mostrar_firma_medico')->default(false);
            $table->boolean('mostrar_sello_centro')->default(false);
            $table->boolean('mostrar_qr_pago')->default(false);
            $table->string('posicion_qr')->default('derecha'); // izquierda, centro, derecha
            
            // Marca de agua
            $table->boolean('mostrar_watermark')->default(false);
            $table->string('texto_watermark')->nullable();
            $table->string('color_watermark', 7)->default('#e5e7eb');
            $table->integer('opacidad_watermark')->default(10); // 0-100
            $table->string('posicion_watermark')->default('centro'); // centro, diagonal
            
            // Configuraciones adicionales
            $table->json('configuracion_adicional')->nullable(); // Para futuras expansiones
            $table->text('css_personalizado')->nullable(); // CSS adicional personalizado
            
            // Relaciones
            $table->foreignId('centro_id')->constrained('centros_medicos'); // Diseño específico por centro
            $table->foreignId('factura_id')->constrained('facturas'); // Relación con facturas si es necesario
            
            // Logs de auditoría
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            
            // Índices
            $table->index(['centro_id', 'activo']);
            $table->index(['es_predeterminado', 'activo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factura_disenos');
    }
};
