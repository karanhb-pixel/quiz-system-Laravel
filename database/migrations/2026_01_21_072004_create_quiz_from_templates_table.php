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
        Schema::create('quiz_from_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_template_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_quiz_attempt_id')->constrained()->onDelete('cascade');
            $table->json('selected_questions'); // Array of selected question IDs
            $table->timestamps();

            $table->index(['quiz_template_id']);
            $table->index(['bank_quiz_attempt_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_from_templates');
    }
};
