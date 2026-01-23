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
        Schema::create('mcq_bank_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_bank_id')->constrained()->onDelete('cascade');
            $table->text('question_text');
            $table->string('option_a');
            $table->string('option_b');
            $table->string('option_c');
            $table->string('option_d');
            $table->char('correct_answer', 1); // 'a', 'b', 'c', or 'd'
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
        Schema::dropIfExists('mcq_bank_questions');
    }
};
