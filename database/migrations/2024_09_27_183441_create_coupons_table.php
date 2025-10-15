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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->uuid('guid')->unique(); // Generar un UUID único
            $table->string('numeric_code', 6)->unique();
            $table->foreignId('event_id')->nullable()->constrained()->onDelete('cascade'); // Relación con la tabla event_id
            $table->foreignId('ticket_type_id')->nullable()->constrained()->onDelete('cascade'); // Relación con la tabla event_id
            $table->boolean('is_consumed')->default(false); // Estado del cupón
            $table->foreignId('event_assistant_id')->nullable()->default(null)->constrained()->onDelete('cascade'); // Relación con la tabla event_assistants
            $table->longText('qrCode')->nullable();
            $table->timestamps();
            $table->unique(['event_id', 'numeric_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
