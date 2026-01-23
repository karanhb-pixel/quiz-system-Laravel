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
        Schema::create('fill_blank_bank_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_bank_id')->constrained()->onDelete('cascade');
            $table->text('question_text');
            $table->text('expected_answer'); // Reference answer for AI
            $table->text('evaluation_hints')->nullable(); // Guidance for AI grading
            $table->boolean('case_sensitive')->default(false);
            $table->integer('points')->default(1);
            $table->string('difficulty')->default('medium'); // 'easy', 'medium', 'hard'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fill_blank_bank_questions');
    }
};
