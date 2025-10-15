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
        Schema::table('event_assistants', function (Blueprint $table) {
            $table->timestamp('entry_time')->nullable()->after('has_entered');
            $table->boolean('rejected')->default(false)->after('entry_time');
            $table->timestamp('rejected_time')->nullable()->after('rejected');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_assistants', function (Blueprint $table) {
            $table->dropColumn('entry_time');
            $table->dropColumn('rejected');
            $table->dropColumn('rejected_time');
        });
    }
};
