<?php

use App\Models\User;
use App\Models\QuestionBank;
use App\Models\BankQuizAttempt;
use App\Services\GeminiService;

test('quiz generates random questions within limit', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id, 'questions_per_quiz' => 3]);

    // Create some questions
    $bank->mcqQuestions()->create([
        'question_text' => 'MCQ Test',
        'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D',
        'correct_answer' => 'a', 'points' => 1
    ]);

    $bank->fillBlankQuestions()->create([
        'question_text' => 'Fill ______ test',
        'expected_answer' => 'blank',
        'points' => 2
    ]);

    $bank->codeQuestions()->create([
        'question_text' => 'Write code',
        'expected_code' => 'echo "test";',
        'language' => 'php',
        'points' => 3
    ]);

    $response = $this->actingAs($user)->get(route('bank-quiz.start', $bank));

    $response->assertViewHas('questions', function($questions) {
        return $questions->count() === 3;
    });
});

test('quiz attempt records are created correctly', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id, 'questions_per_quiz' => 1]);

    // Create a question
    $bank->mcqQuestions()->create([
        'question_text' => 'Test MCQ',
        'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D',
        'correct_answer' => 'a', 'points' => 1
    ]);

    $this->actingAs($user)->get(route('bank-quiz.start', $bank));

    $this->assertDatabaseHas('bank_quiz_attempts', [
        'user_id' => $user->id,
        'question_bank_id' => $bank->id,
        'total_questions' => 1,
        'total_points' => 1
    ]);
});

test('quiz submission calculates scores correctly', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id, 'questions_per_quiz' => 1]);

    // Create MCQ question
    $mcq = $bank->mcqQuestions()->create([
        'question_text' => 'Test MCQ',
        'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D',
        'correct_answer' => 'a', 'points' => 1
    ]);

    // Start quiz
    $this->actingAs($user)->get(route('bank-quiz.start', $bank));
    $attempt = BankQuizAttempt::latest()->first();

    // Submit correct answer
    $response = $this->actingAs($user)->post(route('bank-quiz.submit', $attempt), [
        'answers' => [
            $mcq->id => 'a' // Correct answer
        ]
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('bank_quiz_attempts', [
        'id' => $attempt->id,
        'points_earned' => 1,
        'score_percentage' => 100.0,
        'completed_at' => now()
    ]);
});

test('ai evaluation falls back gracefully', function () {
    // Test that AI service doesn't break the application if API fails
    $service = new GeminiService();

    // This should not throw an exception even if API key is invalid
    $result = $service->evaluateFillBlank(
        'Test question',
        'user answer',
        'expected answer'
    );

    // Should return a fallback result
    expect($result)->toBeArray();
    expect($result)->toHaveKey('is_correct');
    expect($result)->toHaveKey('confidence');
    expect($result)->toHaveKey('feedback');
    expect($result)->toHaveKey('score');
});

test('quiz attempt prevents double submission', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    // Create completed attempt
    $attempt = BankQuizAttempt::create([
        'user_id' => $user->id,
        'question_bank_id' => $bank->id,
        'total_questions' => 1,
        'total_points' => 1,
        'points_earned' => 1,
        'score_percentage' => 100,
        'completed_at' => now()
    ]);

    // Try to submit again
    $response = $this->actingAs($user)->post(route('bank-quiz.submit', $attempt), [
        'answers' => []
    ]);

    $response->assertRedirect(route('bank-quiz.result', $attempt));
});

test('quiz results show ai feedback', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    // Create fill-blank question
    $question = $bank->fillBlankQuestions()->create([
        'question_text' => 'Laravel uses ______ as its templating engine.',
        'expected_answer' => 'Blade',
        'points' => 2
    ]);

    // Create attempt and response
    $attempt = BankQuizAttempt::create([
        'user_id' => $user->id,
        'question_bank_id' => $bank->id,
        'total_questions' => 1,
        'total_points' => 2,
        'points_earned' => 1.5,
        'score_percentage' => 75,
        'completed_at' => now()
    ]);

    $attempt->responses()->create([
        'question_type' => 'fill_blank',
        'question_id' => $question->id,
        'user_answer' => 'Blade',
        'is_correct' => true,
        'points_earned' => 1.5,
        'ai_feedback' => 'Correct! Laravel uses Blade templating.',
        'ai_confidence' => 0.95
    ]);

    $response = $this->actingAs($user)->get(route('bank-quiz.result', $attempt));

    $response->assertViewHas('attempt', function($attempt) {
        return $attempt->responses->count() === 1 &&
               $attempt->responses->first()->ai_feedback !== null;
    });
});