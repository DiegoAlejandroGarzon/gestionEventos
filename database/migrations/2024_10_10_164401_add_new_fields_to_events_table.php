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
        Schema::table('events', function (Blueprint $table) {
            $table->date('event_date_end')->nullable();    // Fecha de finalización del evento
            $table->string('color_one', 7)->nullable();  // Primer color (formato HEX, e.g., #FFFFFF)
            $table->string('color_two', 7)->nullable();  // Segundo color (formato HEX, e.g., #FFFFFF)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            //
        });
    }
};
