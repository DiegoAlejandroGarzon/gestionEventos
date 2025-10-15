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
            $table->unsignedBigInteger('department_id')->nullable()->after('ticket_type_id');

            // Si tienes una tabla de departamentos y quieres clave foránea:
            // $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('event_assistants', function (Blueprint $table) {
            // Si agregaste clave foránea, elimínala primero
            // $table->dropForeign(['department_id']);

            $table->dropColumn('department_id');
        });
    }
};
