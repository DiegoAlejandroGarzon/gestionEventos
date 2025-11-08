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
        Schema::table('conversations_messages', function (Blueprint $table) {
            $table->unique('message_what_id', 'unique_message_what_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations_messages', function (Blueprint $table) {
            $table->dropUnique('unique_message_what_id');
        });
    }
};
