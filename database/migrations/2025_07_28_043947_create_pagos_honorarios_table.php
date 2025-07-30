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
        Schema::create('pagos_honorarios', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('liquidacion_id');
            $table->foreign('liquidacion_id')->references('id')->on('liquidaciones_honorarios');
            $table->timestamp('fecha_pago');
            $table->decimal('monto_pagado', 10, 2);
            $table->enum('metodo_pago', ['TRANSFERENCIA', 'CHEQUE', 'EFECTIVO']);
            $table->string('referencia_bancaria', 255)->nullable();
            $table->decimal('retencion_isr_pct', 5, 2)->default(0);
            $table->decimal('retencion_isr_monto', 10, 2)->default(0);
            $table->text('observaciones')->nullable();
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
        Schema::dropIfExists('pagos_honorarios');
    }
};