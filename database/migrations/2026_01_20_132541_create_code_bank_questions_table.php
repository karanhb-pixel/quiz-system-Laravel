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
        Schema::create('code_bank_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_bank_id')->constrained()->onDelete('cascade');
            $table->text('question_text'); // e.g., "Write CSS for blue background, white text"
            $table->text('expected_code'); // Reference solution
            $table->text('evaluation_criteria')->nullable(); // What to check (syntax, specific properties, etc.)
            $table->string('language')->default('css'); // 'css', 'html', 'javascript', 'python', etc.
            $table->integer('points')->default(2); // Code questions worth more
            $table->string('difficulty')->default('medium'); // 'easy', 'medium', 'hard'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('code_bank_questions');
    }
};
