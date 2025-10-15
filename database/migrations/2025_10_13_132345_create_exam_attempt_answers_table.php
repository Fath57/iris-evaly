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
        Schema::create('exam_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained('exam_attempts')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->foreignId('option_id')->nullable()->constrained('question_options')->onDelete('set null');
            $table->text('answer_text')->nullable(); // For open-ended questions
            $table->boolean('is_correct')->nullable();
            $table->decimal('points_awarded', 5, 2)->default(0);
            $table->integer('time_spent_seconds')->default(0);
            $table->timestamps();
            
            $table->index(['exam_attempt_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_attempt_answers');
    }
};
