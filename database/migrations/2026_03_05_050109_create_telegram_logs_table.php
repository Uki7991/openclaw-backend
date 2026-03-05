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
        Schema::create('telegram_logs', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id')->index();
            $table->string('user_name')->nullable();
            $table->text('message')->nullable();
            $table->text('bot_response')->nullable();
            $table->string('action')->nullable(); // Например 'command', 'api_key_added'
            $table->json('payload')->nullable(); // Полный dump update для дебага
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_logs');
    }
};
