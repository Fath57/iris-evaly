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
        Schema::table('exams', function (Blueprint $table) {
            // Add instructions and advanced display parameters
            $table->text('instructions')->nullable()->after('description');
            $table->boolean('shuffle_options')->default(false)->after('shuffle_questions');
            $table->boolean('allow_review')->default(true)->after('show_results_immediately');
            $table->boolean('show_correct_answers')->default(true)->after('allow_review');
            $table->boolean('require_all_questions')->default(true)->after('show_correct_answers');
            $table->integer('questions_per_page')->default(1)->after('require_all_questions');
            $table->boolean('allow_navigation')->default(true)->after('questions_per_page');
            $table->text('success_message')->nullable()->after('allow_navigation');
            $table->text('failure_message')->nullable()->after('success_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn([
                'instructions',
                'shuffle_options',
                'allow_review',
                'show_correct_answers',
                'require_all_questions',
                'questions_per_page',
                'allow_navigation',
                'success_message',
                'failure_message'
            ]);
        });
    }
};
