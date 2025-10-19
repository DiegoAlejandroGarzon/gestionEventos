<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversationsMessagesTable extends Migration
{
    public function up()
    {
        Schema::create('conversations_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('users_id')->nullable()->index();
            $table->unsignedBigInteger('conversations_id')->index();
            $table->tinyInteger('is_read')->default(0);
            $table->text('content')->nullable()->collation('utf8mb4_unicode_ci');
            $table->text('content_bot')->nullable()->collation('utf8mb4_unicode_ci');
            $table->string('content_response', 255)->nullable()->collation('utf8mb4_unicode_ci');
            $table->enum('direction', ['sent', 'received'])->default('sent')->collation('utf8mb4_unicode_ci');
            $table->string('message_what_id', 255)->nullable()->collation('utf8mb4_unicode_ci');
            $table->string('type', 255)->nullable()->collation('utf8mb4_unicode_ci');
            $table->string('origin', 255)->nullable()->collation('utf8mb4_unicode_ci');
            $table->string('origin_bot_type', 255)->nullable()->collation('utf8mb4_unicode_ci');
            $table->string('url_file', 255)->nullable()->collation('utf8mb4_unicode_ci');
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps(); // created_at y updated_at
            $table->softDeletes(); // deleted_at
            
            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('conversations_id')->references('id')->on('conversations')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('conversations_messages');
    }
}
