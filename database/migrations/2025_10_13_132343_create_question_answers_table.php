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
        Schema::create('question_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->foreignId('option_id')->nullable()->constrained('question_options')->onDelete('cascade');
            $table->text('answer_text')->nullable(); // For open-ended questions
            $table->boolean('is_correct')->default(true);
            $table->text('explanation')->nullable(); // Explanation for this specific answer
            $table->timestamps();
            
            $table->index('question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_answers');
    }
};
