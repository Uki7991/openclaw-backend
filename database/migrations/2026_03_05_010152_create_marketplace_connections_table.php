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
        Schema::create('marketplace_connections', function (Blueprint $construct) {
            $construct->id();
            $construct->foreignId('user_id')->constrained()->cascadeOnDelete();
            $construct->string('marketplace_type'); // 'wb' or 'ozon'
            $construct->text('api_key');
            $construct->boolean('is_active')->default(true);
            $construct->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_connections');
    }
};
