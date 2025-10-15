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
        Schema::create('ai_prompts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('prompt_template');
            $table->string('ai_provider'); // chatgpt, gemini, perplexity
            $table->string('difficulty_level')->nullable(); // easy, medium, hard
            $table->string('question_type')->nullable(); // multiple_choice, true_false, etc.
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->decimal('average_quality_rating', 3, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_prompts');
    }
};
