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
        Schema::create('cai_correlativos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('autorizacion_id')->constrained('cai_autorizaciones');
            $table->string('numero_factura');
            $table->timestamp('fecha_emision');
            $table->foreignId('usuario_id')->constrained('users');
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
        Schema::dropIfExists('cai_correlativos');
    }
};
