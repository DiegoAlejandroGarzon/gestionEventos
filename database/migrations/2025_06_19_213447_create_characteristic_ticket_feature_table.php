<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('characteristic_ticket_feature', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_feature_id')->constrained('ticket_features')->onDelete('cascade');
            $table->foreignId('ticket_characteristic_id')->constrained('ticket_characteristics')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('characteristic_ticket_feature');
    }
};
