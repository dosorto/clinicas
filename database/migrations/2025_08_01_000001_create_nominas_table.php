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
        Schema::create('nominas', function (Blueprint $table) {
            $table->id();
            $table->string('empresa');
            $table->integer('año');
            $table->integer('mes');
            $table->enum('tipo_pago', ['mensual', 'quincenal', 'semanal'])->default('mensual');
            $table->integer('quincena')->nullable(); // 1 = primera quincena, 2 = segunda quincena
            $table->text('descripcion')->nullable();
            $table->boolean('cerrada')->default(false);
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
            $table->unsignedBigInteger('centro_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('centro_id')->references('id')->on('centros_medicos');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->index(['año', 'mes', 'empresa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nominas');
    }
};
