<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\QuestionBank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionBankTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_authorized_user_can_create_a_question_bank()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post(route('question-banks.store'), [
            'name' => 'Laravel Basics',
            'description' => 'Test Bank',
            'category_id' => $category->id,
            'questions_per_quiz' => 10,
        ]);

        $response->assertRedirect(route('question-banks.index'));
        $this->assertDatabaseHas('question_banks', ['name' => 'Laravel Basics']);
    }

    /** @test */
    public function a_slug_is_automatically_generated_from_the_name()
    {
        $bank = QuestionBank::create([
            'name' => 'PHP Advanced',
            'category_id' => Category::factory()->create()->id,
            'user_id' => User::factory()->create()->id,
        ]);

        $this->assertEquals('php-advanced', $bank->slug);
    }

    /** @test */
    public function question_bank_requires_valid_category()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('question-banks.store'), [
            'name' => 'Test Bank',
            'category_id' => 999, // Non-existent category
            'questions_per_quiz' => 10,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('category_id');
    }

    /** @test */
    public function question_bank_total_questions_count_updates_correctly()
    {
        $bank = QuestionBank::factory()->create();

        // Add MCQ question
        $bank->mcqQuestions()->create([
            'question_text' => 'Test MCQ',
            'option_a' => 'A',
            'option_b' => 'B',
            'option_c' => 'C',
            'option_d' => 'D',
            'correct_answer' => 'a',
            'points' => 1
        ]);

        // Add fill-blank question
        $bank->fillBlankQuestions()->create([
            'question_text' => 'Test ______ question',
            'expected_answer' => 'fill',
            'points' => 2
        ]);

        $this->assertEquals(2, $bank->fresh()->total_questions);
    }
}