<?php

use App\Models\User;
use App\Models\QuestionBank;
use App\Models\BankQuizAttempt;
use App\Services\QuizAnalyticsService;

test('analytics service calculates bank statistics', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    // Create some attempts
    BankQuizAttempt::create([
        'user_id' => $user->id,
        'question_bank_id' => $bank->id,
        'total_questions' => 10,
        'total_points' => 10,
        'points_earned' => 8,
        'score_percentage' => 80,
        'completed_at' => now()
    ]);

    BankQuizAttempt::create([
        'user_id' => $user->id,
        'question_bank_id' => $bank->id,
        'total_questions' => 10,
        'total_points' => 10,
        'points_earned' => 6,
        'score_percentage' => 60,
        'completed_at' => now()
    ]);

    $analytics = new QuizAnalyticsService();
    $stats = $analytics->getBankStatistics($bank);

    expect($stats['total_attempts'])->toBe(2);
    expect($stats['average_score'])->toBe(70.0);
    expect($stats['highest_score'])->toBe('80.00');
    expect($stats['lowest_score'])->toBe('60.00');
});

test('analytics service calculates question performance', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    // Create MCQ question
    $mcq = $bank->mcqQuestions()->create([
        'question_text' => 'Test MCQ',
        'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D',
        'correct_answer' => 'a', 'points' => 1
    ]);

    // Create attempts with responses
    $attempt1 = BankQuizAttempt::create([
        'user_id' => $user->id,
        'question_bank_id' => $bank->id,
        'total_questions' => 1,
        'total_points' => 1,
        'points_earned' => 1,
        'score_percentage' => 100,
        'completed_at' => now()
    ]);

    $attempt1->responses()->create([
        'question_type' => 'mcq',
        'question_id' => $mcq->id,
        'user_answer' => 'a',
        'is_correct' => true,
        'points_earned' => 1
    ]);

    $attempt2 = BankQuizAttempt::create([
        'user_id' => $user->id,
        'question_bank_id' => $bank->id,
        'total_questions' => 1,
        'total_points' => 1,
        'points_earned' => 0,
        'score_percentage' => 0,
        'completed_at' => now()
    ]);

    $attempt2->responses()->create([
        'question_type' => 'mcq',
        'question_id' => $mcq->id,
        'user_answer' => 'b',
        'is_correct' => false,
        'points_earned' => 0
    ]);

    $analytics = new QuizAnalyticsService();
    $performance = $analytics->getQuestionPerformance($bank);

    expect($performance)->toHaveCount(1);
    expect($performance[0]['correct_percentage'])->toBe(50.0);
    expect($performance[0]['total_attempts'])->toBe(2);
});

test('analytics dashboard shows correct data', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    // Create some attempts
    BankQuizAttempt::create([
        'user_id' => $user->id,
        'question_bank_id' => $bank->id,
        'total_questions' => 5,
        'total_points' => 5,
        'points_earned' => 4,
        'score_percentage' => 80,
        'completed_at' => now()
    ]);

    $response = $this->actingAs($user)->get(route('bank-analytics.show', $bank));

    $response->assertViewHas('bank');
    $response->assertViewHas('stats');
    $response->assertViewHas('questionPerformance');
    $response->assertViewHas('recentAttempts');
});

test('analytics service handles empty bank', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    $analytics = new QuizAnalyticsService();
    $stats = $analytics->getBankStatistics($bank);

    expect($stats['total_attempts'])->toBe(0);
    expect($stats['average_score'])->toBe(0);
    expect($stats['highest_score'])->toBeNull();
    expect($stats['lowest_score'])->toBeNull();
});

test('analytics service calculates trend data', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    // Create attempts over time
    BankQuizAttempt::create([
        'user_id' => $user->id,
        'question_bank_id' => $bank->id,
        'total_questions' => 10,
        'total_points' => 10,
        'points_earned' => 8,
        'score_percentage' => 80,
        'completed_at' => now()->subDays(2)
    ]);

    BankQuizAttempt::create([
        'user_id' => $user->id,
        'question_bank_id' => $bank->id,
        'total_questions' => 10,
        'total_points' => 10,
        'points_earned' => 9,
        'score_percentage' => 90,
        'completed_at' => now()->subDay()
    ]);

    BankQuizAttempt::create([
        'user_id' => $user->id,
        'question_bank_id' => $bank->id,
        'total_questions' => 10,
        'total_points' => 10,
        'points_earned' => 7,
        'score_percentage' => 70,
        'completed_at' => now()
    ]);

    $analytics = new QuizAnalyticsService();
    $trend = $analytics->getScoreTrend($bank, 7);

    expect($trend)->toHaveCount(3);
    expect($trend[0]['score'])->toBe(80.0);
    expect($trend[1]['score'])->toBe(90.0);
    expect($trend[2]['score'])->toBe(70.0);
});

test('analytics service calculates difficulty distribution', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    // Create questions with different difficulty levels
    $easy = $bank->mcqQuestions()->create([
        'question_text' => 'Easy question',
        'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D',
        'correct_answer' => 'a', 'points' => 1, 'difficulty' => 'easy'
    ]);

    $medium = $bank->mcqQuestions()->create([
        'question_text' => 'Medium question',
        'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D',
        'correct_answer' => 'a', 'points' => 2, 'difficulty' => 'medium'
    ]);

    $hard = $bank->mcqQuestions()->create([
        'question_text' => 'Hard question',
        'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D',
        'correct_answer' => 'a', 'points' => 3, 'difficulty' => 'hard'
    ]);

    $analytics = new QuizAnalyticsService();
    $distribution = $analytics->getDifficultyDistribution($bank);

    expect($distribution['easy'])->toBe(1);
    expect($distribution['medium'])->toBe(1);
    expect($distribution['hard'])->toBe(1);
});