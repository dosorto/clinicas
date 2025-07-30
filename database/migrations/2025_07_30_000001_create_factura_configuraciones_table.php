<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factura_configuraciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medico_id');
            $table->foreign('medico_id')->references('id')->on('medicos');
            $table->unsignedBigInteger('consulta_id')->nullable();
            $table->foreign('consulta_id')->references('id')->on('consultas');
            $table->unsignedBigInteger('centro_id')->nullable();
            $table->foreign('centro_id')->references('id')->on('centros_medicos');

            $table->string('logo')->nullable();
            $table->string('razon_social')->nullable();
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->string('color_primario', 7)->default('#2563eb');
            $table->string('color_secundario', 7)->default('#64748b');
            $table->text('encabezado')->nullable();
            $table->text('pie_pagina')->nullable();
            $table->string('formato_numeracion')->nullable();


            $table->timestamps();
            $table->softDeletes();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
    
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factura_configuraciones');
    }
};
