<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('conversations_messages', function (Blueprint $table) {
            $table->string('params_template')->nullable()->after('url_file');
            // Reemplaza 'your_column' por el nombre de la columna existente que precederá a esta
        });
    }

    public function down(): void
    {
        Schema::table('conversations_messages', function (Blueprint $table) {
            $table->dropColumn('params_template');
        });
    }
};
