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
        Schema::create('feature_consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_assistant_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_feature_id')->constrained()->onDelete('cascade');
            $table->timestamp('consumed_at')->nullable(); // Fecha y hora de consumo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_consumptions');
    }
};
