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
        Schema::table('questions', function (Blueprint $table) {
            // Make exam_id nullable for question bank
            $table->foreignId('exam_id')->nullable()->change();
            
            // Remove JSON columns
            $table->dropColumn(['options', 'correct_answer']);
            
            // Add new fields for enhanced functionality
            $table->text('feedback')->nullable()->after('explanation');
            $table->string('category')->nullable()->after('feedback');
            $table->string('difficulty_level')->default('medium')->after('category'); // easy, medium, hard
            $table->boolean('is_in_bank')->default(false)->after('difficulty_level');
            $table->text('formatting_options')->nullable()->after('is_in_bank'); // For bold, italic, etc.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->json('options')->nullable();
            $table->text('correct_answer');
            $table->dropColumn(['feedback', 'category', 'difficulty_level', 'is_in_bank', 'formatting_options']);
        });
    }
};
