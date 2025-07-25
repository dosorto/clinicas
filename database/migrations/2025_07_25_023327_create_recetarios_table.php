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
            
            // Relaciones principales
            $table->unsignedBigInteger('medico_id');
            $table->foreign('medico_id')->references('id')->on('medicos');
            $table->unsignedBigInteger('consulta_id')->nullable();
            $table->foreign('consulta_id')->references('id')->on('consultas');
            $table->unsignedBigInteger('centro_id')->nullable();
            $table->foreign('centro_id')->references('id')->on('centros_medicos');
            
            // Campos básicos del recetario
            $table->string('numero_recetario', 50)->unique()->nullable();
            $table->text('observaciones_generales')->nullable();
            $table->enum('estado', ['borrador', 'activo', 'suspendido', 'cancelado'])->default('borrador');
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            
            // Auditoría
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
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
