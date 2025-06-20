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
        Schema::create('medicos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('persona_id'); // referencia a persona
            $table->foreign('persona_id')->references('id')->on('personas');
            $table->string('numero_colegiacion')->unique(); // número de colegiación
          

            $table->timestamps(); // created_at y updated_at
            $table->softDeletes(); // deleted_at

            // campos de auditoría
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();

            // Claves foráneas
            
            // Si ya tienes una tabla personas, puedes descomentar esto:
            // $table->foreign('persona_id')->references('id')->on('personas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicos');
    }
};

