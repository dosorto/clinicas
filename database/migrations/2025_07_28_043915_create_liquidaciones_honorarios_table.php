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
        Schema::create('liquidaciones_honorarios', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('medico_id');
            $table->foreign('medico_id')->references('id')->on('medicos');
            $table->date('periodo_inicio');
            $table->date('periodo_fin');
            $table->decimal('monto_total', 10, 2);
            $table->enum('estado', ['PENDIENTE', 'PAGADA', 'PARCIAL', 'ANULADA']);
            $table->enum('tipo_liquidacion', ['PORCENTAJE', 'FIJO', 'MIXTO']);
            $table->unsignedBigInteger('centro_id')->nullable();
            $table->foreign('centro_id')->references('id')->on('centros_medicos');
            
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
        Schema::dropIfExists('liquidaciones_honorarios');
    }
};