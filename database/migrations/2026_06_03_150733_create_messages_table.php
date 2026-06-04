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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
            $table->foreignId('ai_model_id')->nullable()->references('id')->on('ai_models');
            $table->string('role'); 
            $table->text('content');
            $table->integer('tokens_used')->nullable()->default(0);
            $table->boolean('is_error')->default(false);
            $table->foreignId('model_id')->nullable()->references('id')->on('ai_models'); // Nullable because if 'role'=user -> no FK ?
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
