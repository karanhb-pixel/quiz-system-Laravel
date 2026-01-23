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
        Schema::create('bank_question_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_quiz_attempt_id')->constrained()->onDelete('cascade');
            $table->string('question_type'); // 'mcq', 'fill_blank', 'code'
            $table->unsignedBigInteger('question_id'); // Polymorphic reference
            $table->text('user_answer');
            $table->boolean('is_correct');
            $table->decimal('points_earned', 5, 2);
            $table->text('ai_feedback')->nullable(); // AI explanation
            $table->decimal('ai_confidence', 3, 2)->nullable(); // 0.00 - 1.00
            $table->timestamps();

            // Index for polymorphic relationship
            $table->index(['question_type', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_question_responses');
    }
};
