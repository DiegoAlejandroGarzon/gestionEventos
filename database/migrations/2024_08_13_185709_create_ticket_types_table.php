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
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->string('name'); // Nombre del tipo de entrada (Ej: VIP, General, etc.)
            $table->decimal('price', 8, 2); // Precio de la entrada
            $table->text('features')->nullable(); // Características de la entrada (accesos, beneficios, etc.)
            $table->unsignedInteger('capacity'); // Capacidad individual para este tipo de entrada
            $table->timestamps();

            // Definir la relación con la tabla events
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
