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
        Schema::create('recetarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medico_id');
            $table->foreign('medico_id')->references('id')->on('medicos');
            $table->unsignedBigInteger('consulta_id')->nullable();
            $table->foreign('consulta_id')->references('id')->on('consultas');
            $table->unsignedBigInteger('centro_id')->nullable();
            $table->foreign('centro_id')->references('id')->on('centros_medicos');
            
            // Campos básicos del recetario
            $table->boolean('tiene_recetario')->default(false); // Estado de activación del recetario
            $table->string('numero_recetario')->nullable();
            $table->text('observaciones_generales')->nullable();
            $table->enum('estado', ['activo', 'inactivo', 'vencido'])->default('activo');
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            
            // Campos de diseño y personalización
            $table->string('logo')->nullable(); // Ruta del archivo de logo
            $table->string('encabezado_texto')->nullable(); // Texto del encabezado personalizable
            $table->string('pie_pagina')->nullable(); // Texto del pie de página
            $table->string('color_primario', 7)->default('#2563eb'); // Color principal (hex)
            $table->string('color_secundario', 7)->default('#64748b'); // Color secundario (hex)
            $table->string('fuente_familia')->default('Arial'); // Familia de fuente
            $table->integer('fuente_tamano')->default(12); // Tamaño de fuente base
            $table->boolean('mostrar_logo')->default(true); // Mostrar/ocultar logo
            $table->boolean('mostrar_especialidades')->default(true); // Mostrar especialidades del médico
            $table->boolean('mostrar_telefono')->default(true); // Mostrar teléfono
            $table->boolean('mostrar_direccion')->default(true); // Mostrar dirección
            $table->text('texto_adicional')->nullable(); // Texto adicional personalizable
            $table->string('formato_papel')->default('half'); // 'half' = media página, 'full' = página completa
            $table->json('configuracion_avanzada')->nullable(); // JSON para configuraciones adicionales
        
            $table->timestamps();
            $table->softDeletes();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recetarios');
    }
};
