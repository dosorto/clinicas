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
        Schema::create('cuentras_por_cobrars', function (Blueprint $table) {
            $table->id();

            $table->foreignId('factura_id')->constrained('facturas');
            $table->decimal('saldo_pendiente', 12, 2);
            $table->date('fecha_vencimiento');
            $table->enum('estado_cuentas_por_cobrar', ['ABIERTA','VENCIDA','CERRADA','INC incobrable'])->default('ABIERTA');
            $table->foreignId('centro_id')->constrained('centros_medicos');

            /* logs */
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuentras_por_cobrars');
    }
};
