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
        Schema::create('consultas', function (Blueprint $table) {
            $table->id();
    
            $table->foreignId('cita_id');
            $table->foreignId('paciente_id');
            $table->foreignId('medico_id');
    
            $table->text('diagnostico');
            $table->text('tratamiento');
            $table->text('observaciones');
    
            $table->integer('created_by');
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
        Schema::dropIfExists('consultas');
    }
};
