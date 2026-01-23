<?php

use App\Models\User;
use App\Models\QuestionBank;
use App\Models\QuizTemplate;
use App\Models\QuizFromTemplate;
use App\Services\QuizGeneratorService;

test('user can create quiz template', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    // Create enough questions for the template
    $bank->mcqQuestions()->createMany([
        ['question_text' => 'MCQ1', 'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D', 'correct_answer' => 'a', 'points' => 1, 'difficulty' => 'easy'],
        ['question_text' => 'MCQ2', 'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D', 'correct_answer' => 'a', 'points' => 1, 'difficulty' => 'medium'],
        ['question_text' => 'MCQ3', 'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D', 'correct_answer' => 'a', 'points' => 1, 'difficulty' => 'hard']
    ]);

    $bank->fillBlankQuestions()->create([
        'question_text' => 'Fill ______',
        'expected_answer' => 'blank',
        'points' => 1,
        'difficulty' => 'easy'
    ]);

    $bank->codeQuestions()->create([
        'question_text' => 'Write code',
        'expected_code' => 'echo "test";',
        'language' => 'php',
        'points' => 1,
        'difficulty' => 'medium'
    ]);

    $response = $this->actingAs($user)->post(route('quiz-templates.store'), [
        'name' => 'Test Template',
        'description' => 'A test template',
        'question_bank_id' => $bank->id,
        'total_questions' => 5,
        'mcq_count' => 3,
        'fill_blank_count' => 1,
        'code_count' => 1,
        'difficulty_distribution' => [
            'easy' => 2,
            'medium' => 2,
            'hard' => 1
        ],
        'time_limit' => 30,
        'is_public' => false
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('quiz_templates', [
        'name' => 'Test Template',
        'user_id' => $user->id,
        'question_bank_id' => $bank->id
    ]);
});

test('template validation works', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    // Test invalid counts
    $response = $this->actingAs($user)->post(route('quiz-templates.store'), [
        'name' => 'Test Template',
        'question_bank_id' => $bank->id,
        'total_questions' => 5,
        'mcq_count' => 3,
        'fill_blank_count' => 3, // Too many
        'code_count' => 1,
        'difficulty_distribution' => ['easy' => 5],
        'time_limit' => 30
    ]);

    $response->assertSessionHasErrors(['mcq_count', 'fill_blank_count', 'code_count']);
});

test('quiz generator service creates quiz from template', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    // Create questions
    $bank->mcqQuestions()->createMany([
        ['question_text' => 'MCQ1', 'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D', 'correct_answer' => 'a', 'points' => 1, 'difficulty' => 'easy'],
        ['question_text' => 'MCQ2', 'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D', 'correct_answer' => 'a', 'points' => 1, 'difficulty' => 'medium'],
        ['question_text' => 'MCQ3', 'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D', 'correct_answer' => 'a', 'points' => 1, 'difficulty' => 'hard']
    ]);

    $bank->fillBlankQuestions()->create([
        'question_text' => 'Fill ______',
        'expected_answer' => 'blank',
        'points' => 1,
        'difficulty' => 'easy'
    ]);

    $bank->codeQuestions()->create([
        'question_text' => 'Write code',
        'expected_code' => 'echo "test";',
        'language' => 'php',
        'points' => 1,
        'difficulty' => 'medium'
    ]);

    $template = QuizTemplate::create([
        'user_id' => $user->id,
        'question_bank_id' => $bank->id,
        'name' => 'Test Template',
        'config' => [
            'total_questions' => 5,
            'question_distribution' => [
                'mcq' => 3,
                'fill_blank' => 1,
                'code' => 1
            ],
            'difficulty_distribution' => ['easy' => 2, 'medium' => 2, 'hard' => 1],
            'time_limit' => 30,
            'shuffle_questions' => true,
            'show_results_immediately' => false,
            'max_attempts' => 1
        ]
    ]);

    $generator = new QuizGeneratorService();
    $quiz = $generator->generateFromTemplate($template, $user);

    expect($quiz)->toBeInstanceOf(QuizFromTemplate::class);
    expect($quiz->selected_questions)->toHaveCount(5);
    expect($quiz->attempt->total_questions)->toBe(5);
});

test('template cannot be created with insufficient questions', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    // Only create 1 MCQ question
    $bank->mcqQuestions()->create([
        'question_text' => 'MCQ1',
        'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D',
        'correct_answer' => 'a', 'points' => 1, 'difficulty' => 'easy'
    ]);

    $response = $this->actingAs($user)->post(route('quiz-templates.store'), [
        'name' => 'Test Template',
        'question_bank_id' => $bank->id,
        'total_questions' => 5,
        'mcq_count' => 3, // But only 1 available
        'fill_blank_count' => 1,
        'code_count' => 1,
        'difficulty_distribution' => ['easy' => 5],
        'time_limit' => 30
    ]);

    $response->assertSessionHasErrors('mcq_count');
});

test('user can view template list', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    QuizTemplate::create([
        'user_id' => $user->id,
        'question_bank_id' => $bank->id,
        'name' => 'Template 1',
        'config' => [
            'total_questions' => 5,
            'question_distribution' => [
                'mcq' => 3,
                'fill_blank' => 1,
                'code' => 1
            ],
            'difficulty_distribution' => ['easy' => 3, 'medium' => 2],
            'time_limit' => 30,
            'shuffle_questions' => true,
            'show_results_immediately' => false,
            'max_attempts' => 1
        ]
    ]);

    $response = $this->actingAs($user)->get(route('quiz-templates.index'));

    $response->assertViewHas('templates');
    $response->assertViewHas('banks');
});

test('template can be made public', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    $template = QuizTemplate::create([
        'user_id' => $user->id,
        'question_bank_id' => $bank->id,
        'name' => 'Public Template',
        'config' => [
            'total_questions' => 5,
            'question_distribution' => [
                'mcq' => 3,
                'fill_blank' => 1,
                'code' => 1
            ],
            'difficulty_distribution' => ['easy' => 3, 'medium' => 2],
            'time_limit' => 30,
            'shuffle_questions' => true,
            'show_results_immediately' => false,
            'max_attempts' => 1
        ],
        'is_public' => true
    ]);

    expect($template->is_public)->toBeTrue();
});

test('quiz from template tracks usage', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    $template = QuizTemplate::create([
        'user_id' => $user->id,
        'question_bank_id' => $bank->id,
        'name' => 'Usage Template',
        'config' => [
            'total_questions' => 3,
            'question_distribution' => [
                'mcq' => 3,
                'fill_blank' => 0,
                'code' => 0
            ],
            'difficulty_distribution' => ['easy' => 3],
            'time_limit' => 30,
            'shuffle_questions' => true,
            'show_results_immediately' => false,
            'max_attempts' => 1
        ]
    ]);

    // Create some questions
    $bank->mcqQuestions()->createMany([
        ['question_text' => 'MCQ1', 'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D', 'correct_answer' => 'a', 'points' => 1, 'difficulty' => 'easy'],
        ['question_text' => 'MCQ2', 'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D', 'correct_answer' => 'a', 'points' => 1, 'difficulty' => 'easy'],
        ['question_text' => 'MCQ3', 'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D', 'correct_answer' => 'a', 'points' => 1, 'difficulty' => 'easy']
    ]);

    $generator = new QuizGeneratorService();
    $quiz1 = $generator->generateFromTemplate($template, $user);
    $quiz2 = $generator->generateFromTemplate($template, $user);

    expect($template->fresh()->usage_count)->toBe(2);
});

test('template deletion cascades to generated quizzes', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    $template = QuizTemplate::create([
        'user_id' => $user->id,
        'question_bank_id' => $bank->id,
        'name' => 'Cascade Template',
        'config' => [
            'total_questions' => 1,
            'question_distribution' => [
                'mcq' => 1,
                'fill_blank' => 0,
                'code' => 0
            ],
            'difficulty_distribution' => ['easy' => 1],
            'time_limit' => 30,
            'shuffle_questions' => true,
            'show_results_immediately' => false,
            'max_attempts' => 1
        ]
    ]);

    // Create question and quiz
    $bank->mcqQuestions()->create([
        'question_text' => 'MCQ1',
        'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D',
        'correct_answer' => 'a', 'points' => 1, 'difficulty' => 'easy'
    ]);

    $generator = new QuizGeneratorService();
    $quiz = $generator->generateFromTemplate($template, $user);

    $template->delete();

    $this->assertDatabaseMissing('quiz_from_templates', ['id' => $quiz->id]);
});