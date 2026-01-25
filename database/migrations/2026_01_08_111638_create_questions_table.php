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
            $table->foreignId('quiz_id')
                  ->constrained('quizzes')
                  ->onDelete('cascade');
            
            $table->text('question_text');
            $table->string('slug')->unique(); // From add_slug migration

            // Options (Nullable for non-MCQ)
            $table->string('a')->nullable();
            $table->string('b')->nullable();
            $table->string('c')->nullable();
            $table->string('d')->nullable();

            // Correct Answer (Text for flexibility)
            $table->text('correct_answer');
            
            // AI & Categorization fields
            $table->string('topic')->nullable();
            $table->string('difficulty')->nullable();
            $table->string('question_type')->default('mcq');
            $table->boolean('generated_by_ai')->default(false);
            $table->unsignedBigInteger('category_id')->nullable();

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
