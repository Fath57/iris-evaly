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
            $table->timestamp('results_released_at')->nullable()->after('show_results_immediately');
            $table->foreignId('results_released_by')->nullable()->after('results_released_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign(['results_released_by']);
            $table->dropColumn(['results_released_at', 'results_released_by']);
        });
    }
};
