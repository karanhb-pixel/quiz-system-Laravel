<?php

use App\Models\User;
use App\Models\Quiz;
use App\Models\Category;
use App\Models\Question;
use App\Models\Result;
use App\Services\GeminiService;

test('admins can create a quiz', function () {
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'email_verified_at' => now()
    ]);
    
    $category = Category::create([
        'name' => 'Technology', 
        'slug' => 'technology', 
        'creator' => $admin->name
    ]);

    $response = $this->actingAs($admin)->post(route('quizzes.store'), [
        'title' => 'PHP Basics',
        'category_id' => $category->id,
    ]);

    $response->assertRedirect(route('quizzes.index'));
    $this->assertDatabaseHas('quizzes', ['title' => 'PHP Basics']);
});

test('students can submit a quiz and get a pending result', function () {
    $user = User::create([
        'name' => 'Student User',
        'email' => 'student@test.com',
        'password' => bcrypt('password'),
        'role' => 'user',
        'email_verified_at' => now()
    ]);
    
    $category = Category::create(['name' => 'General', 'slug' => 'general', 'creator' => 'Admin']);
    $quiz = Quiz::create(['title' => 'Sample Quiz', 'category_id' => $category->id, 'user_id' => $user->id]);
    
    $question = Question::create([
        'quiz_id' => $quiz->id,
        'question_text' => 'What is 1+1?',
        'question_type' => 'mcq',
        'a' => '1', 'b' => '2', 'c' => '3', 'd' => '4',
        'correct_answer' => 'b'
    ]);

    $response = $this->actingAs($user)->post(route('quizzes.submit', $quiz->slug), [
        'answers' => [
            $question->id => 'b'
        ]
    ]);

    $result = Result::first();
    $response->assertRedirect(route('quizzes.result', $result->id));
    expect($result->status)->toBe('pending');
});

test('code normalization (fast check) works correctly', function () {
    $user = User::create([
        'name' => 'Coder User',
        'email' => 'coder@test.com',
        'password' => bcrypt('password'),
        'role' => 'user',
        'email_verified_at' => now()
    ]);
    
    $category = Category::create(['name' => 'Coding', 'slug' => 'coding', 'creator' => 'Admin']);
    $quiz = Quiz::create(['title' => 'Code Quiz', 'category_id' => $category->id, 'user_id' => $user->id]);
    
    $question = Question::create([
        'quiz_id' => $quiz->id,
        'question_text' => 'Write a function',
        'question_type' => 'code',
        'correct_answer' => "<?php function test() { return true; } // comment ?>"
    ]);

    $result = Result::create([
        'user_id' => $user->id,
        'quiz_id' => $quiz->id,
        'total_questions' => 1,
        'correct_answers' => 0,
        'score_percentage' => 0,
        'user_answers' => json_encode([$question->id => "<?php\n function test() {\n return true;\n }"]),
        'status' => 'pending'
    ]);

    $response = $this->actingAs($user)->post(route('quizzes.evaluate', $result->id));

    $response->assertStatus(200);
    $data = $response->json();
    
    expect($data['status'])->toBe('completed');
    expect($data['score'])->toBe(1); 
    expect($data['aiEvaluations'][$question->id]['is_correct'])->toBeTrue();
    expect($data['aiEvaluations'][$question->id]['explanation'])->not->toBeEmpty();
});

