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
    Schema::create('sensor_data', function (Blueprint $table) {
        $table->id();
        $table->float('temperatura')->nullable();
        $table->float('humedad')->nullable();
        $table->float('peso')->nullable();
        $table->boolean('alerta_termica')->default(false);
        $table->boolean('bomba_encendida')->default(false);
        $table->boolean('alerta_peso')->default(false);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_data');
    }
};
