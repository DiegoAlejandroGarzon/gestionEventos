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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_assistant_id')->constrained('event_assistants');
            $table->string('payer_name');
            $table->string('payer_document_type');
            $table->string('payer_document_number');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method');
            $table->string('payment_proof')->nullable(); // Para almacenar la imagen de la transferencia
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
