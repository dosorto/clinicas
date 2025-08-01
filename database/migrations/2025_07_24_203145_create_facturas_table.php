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
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('paciente_id')->constrained('pacientes');
            $table->foreignId('cita_id')->nullable()->constrained('citas');
            $table->foreignId('consulta_id')->nullable()->constrained('consultas');
            $table->foreignId('medico_id')->constrained('medicos');
            $table->date('fecha_emision');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('descuento_total', 12, 2)->default(0);
            $table->decimal('impuesto_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->enum('estado', ['PENDIENTE','PAGADA','ANULADA','PARCIAL'])->default('PENDIENTE');
            $table->text('observaciones')->nullable();
            $table->foreignId('centro_id')->constrained('centros_medicos');
            $table->unsignedBigInteger('numero_factura')->unique();

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
        Schema::dropIfExists('facturas');
    }
};
