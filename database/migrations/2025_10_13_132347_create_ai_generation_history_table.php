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
        Schema::create('ai_generation_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('prompt_id')->nullable()->constrained('ai_prompts')->onDelete('set null');
            $table->string('ai_provider'); // chatgpt, gemini, perplexity
            $table->text('subject_theme');
            $table->string('difficulty_level');
            $table->integer('questions_requested');
            $table->integer('questions_generated');
            $table->integer('questions_accepted')->default(0);
            $table->text('custom_prompt')->nullable();
            $table->decimal('quality_rating', 3, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->integer('tokens_used')->nullable();
            $table->decimal('cost', 10, 6)->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index('ai_provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_generation_history');
    }
};
