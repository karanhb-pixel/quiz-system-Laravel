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
        // Add AI metadata to MCQ questions table
        Schema::table('mcq_bank_questions', function (Blueprint $table) {
            $table->boolean('generated_by_ai')->default(false)->after('points');
            $table->boolean('needs_review')->default(false)->after('generated_by_ai');
            $table->integer('quality_score')->nullable()->after('needs_review');
            $table->json('generation_metadata')->nullable()->after('quality_score');
        });

        // Add AI metadata to fill-blank questions table
        Schema::table('fill_blank_bank_questions', function (Blueprint $table) {
            $table->boolean('generated_by_ai')->default(false)->after('points');
            $table->boolean('needs_review')->default(false)->after('generated_by_ai');
            $table->integer('quality_score')->nullable()->after('needs_review');
            $table->json('generation_metadata')->nullable()->after('quality_score');
        });

        // Add AI metadata to code questions table
        Schema::table('code_bank_questions', function (Blueprint $table) {
            $table->boolean('generated_by_ai')->default(false)->after('points');
            $table->boolean('needs_review')->default(false)->after('generated_by_ai');
            $table->integer('quality_score')->nullable()->after('needs_review');
            $table->json('generation_metadata')->nullable()->after('quality_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove AI metadata from MCQ questions table
        Schema::table('mcq_bank_questions', function (Blueprint $table) {
            $table->dropColumn(['generated_by_ai', 'needs_review', 'quality_score', 'generation_metadata']);
        });

        // Remove AI metadata from fill-blank questions table
        Schema::table('fill_blank_bank_questions', function (Blueprint $table) {
            $table->dropColumn(['generated_by_ai', 'needs_review', 'quality_score', 'generation_metadata']);
        });

        // Remove AI metadata from code questions table
        Schema::table('code_bank_questions', function (Blueprint $table) {
            $table->dropColumn(['generated_by_ai', 'needs_review', 'generation_metadata']);
        });
    }
};
