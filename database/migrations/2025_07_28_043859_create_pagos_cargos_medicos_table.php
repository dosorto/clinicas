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
        Schema::create('pagos_cargos_medicos', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('cargo_id');
            $table->foreign('cargo_id')->references('id')->on('cargos_medicos');
            $table->timestamp('fecha_pago');
            $table->decimal('monto_pagado', 10, 2);
            $table->enum('metodo_pago', ['EFECTIVO', 'TRANSFERENCIA', 'CHEQUE']);
            $table->string('referencia', 255)->nullable();
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
        Schema::dropIfExists('pagos_cargos_medicos');
    }
};