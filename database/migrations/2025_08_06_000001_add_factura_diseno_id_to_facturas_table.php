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
        Schema::table('facturas', function (Blueprint $table) {
            // Agregar relación con factura_disenos
            $table->foreignId('factura_diseno_id')
                  ->nullable()
                  ->after('centro_id')
                  ->constrained('factura_disenos')
                  ->onDelete('set null');
                  
            // Índice para mejorar el rendimiento
            $table->index(['factura_diseno_id', 'centro_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropForeign(['factura_diseno_id']);
            $table->dropIndex(['factura_diseno_id', 'centro_id']);
            $table->dropColumn('factura_diseno_id');
        });
    }
};
