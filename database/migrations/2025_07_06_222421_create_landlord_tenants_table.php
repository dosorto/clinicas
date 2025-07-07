<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("centro_id")->unique();
            $table->foreign("centro_id")->references("id")->on("centros_medicos");
            $table->string('name');
            $table->string('domain')->unique();
            $table->string('database');
            $table->timestamps();
        });
    }
};
