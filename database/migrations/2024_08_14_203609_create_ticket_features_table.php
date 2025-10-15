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
        Schema::create('ticket_features', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('featured')->default(false);
            $table->timestamps();
        });

        // Crear tabla intermedia para la relación many-to-many entre ticket_types y ticket_features
        Schema::create('ticket_type_feature', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_type_id');
            $table->unsignedBigInteger('ticket_feature_id');
            $table->timestamps();

            $table->foreign('ticket_type_id')->references('id')->on('ticket_types')->onDelete('cascade');
            $table->foreign('ticket_feature_id')->references('id')->on('ticket_features')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_type_feature');
        Schema::dropIfExists('ticket_features');
    }
};
