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
        Schema::create('teacher_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('set null');
            $table->boolean('is_main_teacher')->default(false); // Professeur principal
            $table->timestamps();

            $table->unique(['teacher_id', 'class_id', 'subject_id']);
        });

        // Remove teacher_id from classes table since we use the pivot now
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn('teacher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore teacher_id in classes table
        Schema::table('classes', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable()->after('description')->constrained('users')->nullOnDelete();
        });

        Schema::dropIfExists('teacher_classes');
    }
};