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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->enum('type', ['multiple_choice', 'true_false', 'short_answer', 'essay'])->default('multiple_choice');
            $table->text('question_text');
            $table->json('options')->nullable(); // For multiple choice
            $table->text('correct_answer');
            $table->integer('points')->default(1);
            $table->integer('order')->default(0);
            $table->text('explanation')->nullable();
            $table->string('image_path')->nullable(); // Image pour l'énoncé
            $table->string('alt_text')->nullable(); // Texte alternatif pour accessibilité
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
