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
        Schema::create('detalle_nominas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nomina_id')->constrained('nominas')->onDelete('cascade');
            $table->foreignId('medico_id')->constrained('medicos')->onDelete('cascade');
            $table->string('medico_nombre');
            $table->decimal('salario_base', 10, 2);
            $table->decimal('deducciones', 10, 2)->default(0);
            $table->decimal('percepciones', 10, 2)->default(0);
            $table->decimal('total_pagar', 10, 2);
            $table->text('deducciones_detalle')->nullable();
            $table->text('percepciones_detalle')->nullable();
            $table->unsignedBigInteger('centro_id');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('centro_id')->references('id')->on('centros_medicos');
            $table->index(['nomina_id', 'medico_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_nominas');
    }
};
