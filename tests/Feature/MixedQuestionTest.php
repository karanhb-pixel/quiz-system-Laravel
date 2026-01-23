<?php

use App\Models\User;
use App\Models\QuestionBank;

test('user can add fill blank question to bank', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('bank-questions.store', $bank), [
        'question_type' => 'fill_blank',
        'question_text' => 'Laravel is a ______ framework.',
        'expected_answer' => 'PHP',
        'points' => 2,
    ]);

    expect($response->getStatusCode())->toBe(302); // Redirect on success
    $this->assertDatabaseHas('fill_blank_bank_questions', ['expected_answer' => 'PHP']);
});

test('user can add code question to bank', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('bank-questions.store', $bank), [
        'question_type' => 'code',
        'question_text' => 'Write CSS to center text.',
        'expected_code' => 'text-align: center;',
        'language' => 'css',
        'points' => 3,
    ]);

    expect($response->getStatusCode())->toBe(302);
    $this->assertDatabaseHas('code_bank_questions', ['language' => 'css']);
});

test('mcq question validation works', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    // Test missing option
    $response = $this->actingAs($user)->post(route('bank-questions.store', $bank), [
        'question_type' => 'mcq',
        'question_text' => 'Test question?',
        'option_a' => 'A',
        'option_b' => 'B',
        'option_c' => 'C',
        // Missing option_d
        'correct_answer' => 'a',
        'points' => 1,
    ]);

    $response->assertSessionHasErrors('option_d');
});

test('code question language validation works', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    // Test invalid language
    $response = $this->actingAs($user)->post(route('bank-questions.store', $bank), [
        'question_type' => 'code',
        'question_text' => 'Write code',
        'expected_code' => 'console.log("test");',
        'language' => 'invalid_lang',
        'points' => 3,
    ]);

    $response->assertSessionHasErrors('language');
});

test('question type routing works', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    // Test MCQ creation route
    $response = $this->actingAs($user)->get(route('bank-questions.create', [$bank, 'mcq']));
    $response->assertViewHas('type', 'mcq');

    // Test fill-blank creation route
    $response = $this->actingAs($user)->get(route('bank-questions.create', [$bank, 'fill_blank']));
    $response->assertViewHas('type', 'fill_blank');
});

test('ai question generation works', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    // Test AI question generation route access
    $response = $this->actingAs($user)->get(route('ai-questions.create', $bank));
    expect($response->getStatusCode())->toBe(200);

    // Test AI MCQ generation (will use fallback since no API key)
    $response = $this->actingAs($user)->post(route('ai-questions.generate', $bank), [
        'question_type' => 'mcq',
        'topic' => 'Laravel',
        'difficulty' => 'medium'
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'question' => [
            'question_text',
            'option_a',
            'option_b',
            'option_c',
            'option_d',
            'correct_answer',
            'generated_by_ai'
        ]
    ]);
});

test('ai question saving works', function () {
    $user = User::factory()->create();
    $bank = QuestionBank::factory()->create(['user_id' => $user->id]);

    $questionData = [
        'question_text' => 'What is Laravel?',
        'option_a' => 'A PHP framework',
        'option_b' => 'A JavaScript library',
        'option_c' => 'A database',
        'option_d' => 'An operating system',
        'correct_answer' => 'a',
        'explanation' => 'Laravel is a PHP framework',
        'difficulty' => 'easy',
        'topic' => 'Laravel',
        'generated_by_ai' => true,
        'points' => 1
    ];

    $response = $this->actingAs($user)->post(route('ai-questions.store', $bank), [
        'question_type' => 'mcq',
        'question_data' => $questionData,
        'confirm_save' => true
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('mcq_bank_questions', [
        'question_text' => 'What is Laravel?',
        'generated_by_ai' => true
    ]);
});