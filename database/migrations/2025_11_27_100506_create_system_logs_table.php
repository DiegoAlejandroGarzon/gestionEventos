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
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');           // Ej: "Usuario ingresó al sistema"
            $table->string('module');           // Ej: "auth", "event", "assistant", etc.
            $table->string('ip')->nullable();
            $table->string('browser')->nullable();
            $table->string('result');           // success | error
            $table->text('details')->nullable(); // datos adicionales opcionales
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
