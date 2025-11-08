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
        Schema::create('numbers_whatsapp_sys_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('sys_users_id')->nullable()->index();
            $table->string('whatsapp_number', 20)->unique()->index();
            $table->string('phone_number_id', 255)->nullable();
            $table->string('whatsapp_business_id', 255)->nullable();
            $table->text('token')->nullable();
            $table->text('template_waba')->nullable();
            $table->string('alias', 255)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes(); // para deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('numbers_whatsapp_sys_user');
    }
};
