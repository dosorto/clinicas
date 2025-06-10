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
        Schema::create('especialidads', function (Blueprint $table) {
            $table->id(); // ID autoincremental
            $table->string('especialidad'); // nombre de la especialidad
            $table->timestamps(); // created_at y updated_at
            $table->softDeletes(); // deleted_at

            // campos de auditoría
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
        Schema::dropIfExists('especialidads');
    }
};
