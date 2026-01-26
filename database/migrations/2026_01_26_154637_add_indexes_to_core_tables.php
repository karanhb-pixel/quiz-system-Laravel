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
        Schema::table('categories', function (Blueprint $table) {
            $table->index('slug');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->index('slug');
            $table->index('user_id');
            $table->index('category_id');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->index('quiz_id');
            $table->index('category_id');
            $table->index('topic');
        });

        Schema::table('results', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('quiz_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['slug']);
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['category_id']);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex(['quiz_id']);
            $table->dropIndex(['category_id']);
            $table->dropIndex(['topic']);
        });

        Schema::table('results', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['quiz_id']);
        });
    }
};
