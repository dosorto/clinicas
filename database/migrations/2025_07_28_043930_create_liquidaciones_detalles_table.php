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
        Schema::create('liquidaciones_detalles', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('liquidacion_id');
            $table->foreign('liquidacion_id')->references('id')->on('liquidaciones_honorarios');
            $table->unsignedBigInteger('factura_detalle_id');
            $table->foreign('factura_detalle_id')->references('id')->on('factura_detalles');
            $table->decimal('porcentaje_honorario', 5, 2);
            $table->decimal('monto_honorario', 10, 2);
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
        Schema::dropIfExists('liquidaciones_detalles');
    }
};