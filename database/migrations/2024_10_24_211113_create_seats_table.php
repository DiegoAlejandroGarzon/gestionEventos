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
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_type_id');
            $table->string('row'); // Fila
            $table->string('column'); // Columna
            $table->boolean('is_assigned')->default(false); // Estado de asignación
            $table->unsignedBigInteger('event_assistant_id')->nullable(); // Asignado a un asistente
            $table->timestamps();

            // Relaciones
            $table->foreign('ticket_type_id')->references('id')->on('ticket_types')->onDelete('cascade');
            $table->foreign('event_assistant_id')->references('id')->on('event_assistants')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
