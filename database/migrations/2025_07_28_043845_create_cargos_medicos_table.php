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
        Schema::create('cargos_medicos', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('medico_id');
            $table->foreign('medico_id')->references('id')->on('medicos');
            $table->unsignedBigInteger('contrato_id');
            $table->foreign('contrato_id')->references('id')->on('contratos_medicos');
            $table->text('descripcion');
            $table->date('periodo_inicio');
            $table->date('periodo_fin');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('impuesto_total', 10, 2);
            $table->decimal('total', 10, 2);
            $table->enum('estado', ['PENDIENTE', 'PAGADA', 'ANULADA', 'PARCIAL']);
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
        Schema::dropIfExists('cargos_medicos');
    }
};