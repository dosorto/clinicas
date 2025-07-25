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
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');
            $table->string('codigo')->unique();
            $table->string('descripcion')->nullable();
            $table->decimal('precio_unitario', 12, 2);
            $table->foreignId('impuesto_id')->nullable()->constrained('impuestos');
            $table->enum('es_exonerado', ['SI', 'NO'])->default('NO');
            $table->decimal('porcentaje_impuesto', 5, 2)->nullable();
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
        Schema::dropIfExists('servicios');
    }
};
