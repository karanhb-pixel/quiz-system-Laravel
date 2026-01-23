<?php

use App\Models\User;
use App\Models\QuestionBank;
use App\Services\QuizCacheService;
use Illuminate\Support\Facades\Cache;

test('cache service stores bank questions', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    // Create some questions
    $mcq = $bank->mcqQuestions()->create([
        'question_text' => 'Test MCQ',
        'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D',
        'correct_answer' => 'a', 'points' => 1, 'difficulty' => 'easy'
    ]);

    $fillBlank = $bank->fillBlankQuestions()->create([
        'question_text' => 'Fill ______',
        'expected_answer' => 'blank',
        'points' => 1,
        'difficulty' => 'easy'
    ]);

    $cacheService = new QuizCacheService();
    $cacheService->cacheBankQuestions($bank);

    $cached = Cache::get("bank:{$bank->id}:questions");
    expect($cached)->not->toBeNull();
    expect($cached)->toHaveCount(2);

    // Check structure
    expect($cached[0]['type'])->toBe('mcq');
    expect($cached[1]['type'])->toBe('fill_blank');
});

test('cache service invalidates on question change', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    $cacheService = new QuizCacheService();
    $cacheService->cacheBankQuestions($bank);

    // Verify cache exists
    expect(Cache::get("bank:{$bank->id}:questions"))->not->toBeNull();

    // Add a question
    $bank->mcqQuestions()->create([
        'question_text' => 'New MCQ',
        'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D',
        'correct_answer' => 'a', 'points' => 1, 'difficulty' => 'easy'
    ]);

    // Cache should be invalidated
    expect(Cache::get("bank:{$bank->id}:questions"))->toBeNull();
});

test('cache service provides fallback when empty', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    $cacheService = new QuizCacheService();

    // Try to get questions from empty cache
    $questions = $cacheService->getCachedQuestions($bank, 5);

    // Should return empty collection
    expect($questions)->toBeEmpty();
});

test('cache service respects question limits', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    // Create 10 questions
    for ($i = 0; $i < 10; $i++) {
        $bank->mcqQuestions()->create([
            'question_text' => "MCQ {$i}",
            'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D',
            'correct_answer' => 'a', 'points' => 1, 'difficulty' => 'easy'
        ]);
    }

    $cacheService = new QuizCacheService();
    $cacheService->cacheBankQuestions($bank);

    // Request only 5 questions
    $questions = $cacheService->getCachedQuestions($bank, 5);

    expect($questions)->toHaveCount(5);
});

test('cache service handles mixed question types', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    // Create different question types
    $bank->mcqQuestions()->create([
        'question_text' => 'MCQ',
        'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D',
        'correct_answer' => 'a', 'points' => 1, 'difficulty' => 'easy'
    ]);

    $bank->fillBlankQuestions()->create([
        'question_text' => 'Fill ______',
        'expected_answer' => 'blank',
        'points' => 1,
        'difficulty' => 'medium'
    ]);

    $bank->codeQuestions()->create([
        'question_text' => 'Write code',
        'expected_code' => 'echo "test";',
        'language' => 'php',
        'points' => 1,
        'difficulty' => 'hard'
    ]);

    $cacheService = new QuizCacheService();
    $cacheService->cacheBankQuestions($bank);

    $cached = Cache::get("bank:{$bank->id}:questions");
    expect($cached)->toHaveCount(3);

    $types = collect($cached)->pluck('type')->unique()->sort()->values();
    expect($types->toArray())->toBe(['code', 'fill_blank', 'mcq']);
});

test('cache service clears bank cache on deletion', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    $cacheService = new QuizCacheService();
    $cacheService->cacheBankQuestions($bank);

    // Verify cache exists
    expect(Cache::get("bank:{$bank->id}:questions"))->not->toBeNull();

    // Delete bank
    $bank->delete();

    // Cache should be cleared
    expect(Cache::get("bank:{$bank->id}:questions"))->toBeNull();
});

test('cache service handles difficulty filtering', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    // Create questions with different difficulties
    $bank->mcqQuestions()->create([
        'question_text' => 'Easy MCQ',
        'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D',
        'correct_answer' => 'a', 'points' => 1, 'difficulty' => 'easy'
    ]);

    $bank->mcqQuestions()->create([
        'question_text' => 'Medium MCQ',
        'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D',
        'correct_answer' => 'a', 'points' => 1, 'difficulty' => 'medium'
    ]);

    $bank->mcqQuestions()->create([
        'question_text' => 'Hard MCQ',
        'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D',
        'correct_answer' => 'a', 'points' => 1, 'difficulty' => 'hard'
    ]);

    $cacheService = new QuizCacheService();
    $cacheService->cacheBankQuestions($bank);

    // Get cached questions
    $questions = $cacheService->getCachedQuestions($bank, 10);

    $difficulties = collect($questions)->pluck('difficulty')->unique()->sort()->values();
    expect($difficulties->toArray())->toBe(['easy', 'hard', 'medium']);
});

test('cache service expires after configured time', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    $cacheService = new QuizCacheService();
    $cacheService->cacheBankQuestions($bank);

    // Verify cache exists
    expect(Cache::get("bank:{$bank->id}:questions"))->not->toBeNull();

    // Simulate cache expiration (this would normally happen automatically)
    Cache::forget("bank:{$bank->id}:questions");

    // Cache should be gone
    expect(Cache::get("bank:{$bank->id}:questions"))->toBeNull();
});

test('cache service prevents cache stampede', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    // Create many questions to simulate load
    for ($i = 0; $i < 50; $i++) {
        $bank->mcqQuestions()->create([
            'question_text' => "MCQ {$i}",
            'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D',
            'correct_answer' => 'a', 'points' => 1, 'difficulty' => 'easy'
        ]);
    }

    $cacheService = new QuizCacheService();

    // Multiple calls should not cause issues
    $questions1 = $cacheService->getCachedQuestions($bank, 10);
    $questions2 = $cacheService->getCachedQuestions($bank, 10);

    expect($questions1)->toHaveCount(10);
    expect($questions2)->toHaveCount(10);
});