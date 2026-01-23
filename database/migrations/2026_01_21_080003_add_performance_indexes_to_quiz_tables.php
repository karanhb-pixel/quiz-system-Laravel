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
        // Add indexes to bank_quiz_attempts table
        Schema::table('bank_quiz_attempts', function (Blueprint $table) {
            $table->index(['question_bank_id', 'created_at'], 'bank_quiz_attempts_bank_created_idx');
            $table->index(['user_id', 'created_at'], 'bank_quiz_attempts_user_created_idx');
            $table->index(['is_completed', 'created_at'], 'bank_quiz_attempts_completed_created_idx');
        });

        // Add indexes to bank_question_responses table
        Schema::table('bank_question_responses', function (Blueprint $table) {
            $table->index(['bank_quiz_attempt_id', 'created_at'], 'responses_attempt_created_idx');
            $table->index(['question_type', 'is_correct'], 'responses_type_correct_idx');
        });

        // Add indexes to question_banks table
        Schema::table('question_banks', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'question_banks_user_created_idx');
            $table->index(['category_id'], 'question_banks_category_idx');
        });

        // Add indexes to mcq_bank_questions table
        Schema::table('mcq_bank_questions', function (Blueprint $table) {
            $table->index(['question_bank_id', 'created_at'], 'mcq_questions_bank_created_idx');
        });

        // Add indexes to fill_blank_bank_questions table
        Schema::table('fill_blank_bank_questions', function (Blueprint $table) {
            $table->index(['question_bank_id', 'created_at'], 'fill_blank_questions_bank_created_idx');
        });

        // Add indexes to code_bank_questions table
        Schema::table('code_bank_questions', function (Blueprint $table) {
            $table->index(['question_bank_id', 'created_at'], 'code_questions_bank_created_idx');
            $table->index(['language'], 'code_questions_language_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes from bank_quiz_attempts table
        Schema::table('bank_quiz_attempts', function (Blueprint $table) {
            $table->dropIndex('bank_quiz_attempts_bank_created_idx');
            $table->dropIndex('bank_quiz_attempts_user_created_idx');
            $table->dropIndex('bank_quiz_attempts_completed_created_idx');
        });

        // Drop indexes from bank_question_responses table
        Schema::table('bank_question_responses', function (Blueprint $table) {
            $table->dropIndex('responses_attempt_created_idx');
            $table->dropIndex('responses_type_correct_idx');
        });

        // Drop indexes from question_banks table
        Schema::table('question_banks', function (Blueprint $table) {
            $table->dropIndex('question_banks_user_created_idx');
            $table->dropIndex('question_banks_category_idx');
        });

        // Drop indexes from mcq_bank_questions table
        Schema::table('mcq_bank_questions', function (Blueprint $table) {
            $table->dropIndex('mcq_questions_bank_created_idx');
        });

        // Drop indexes from fill_blank_bank_questions table
        Schema::table('fill_blank_bank_questions', function (Blueprint $table) {
            $table->dropIndex('fill_blank_questions_bank_created_idx');
        });

        // Drop indexes from code_bank_questions table
        Schema::table('code_bank_questions', function (Blueprint $table) {
            $table->dropIndex('code_questions_bank_created_idx');
            $table->dropIndex('code_questions_language_idx');
        });
    }
};
