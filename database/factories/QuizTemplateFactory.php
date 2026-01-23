<?php

namespace Database\Factories;

use App\Models\QuestionBank;
use App\Models\User;
use App\Models\QuizTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuizTemplate>
 */
class QuizTemplateFactory extends Factory
{
    protected $model = QuizTemplate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'question_bank_id' => QuestionBank::factory(),
            'user_id' => User::factory(),
            'config' => [
                'question_distribution' => [
                    'mcq' => 25,
                    'fill_blank' => 25,
                    'code' => 50
                ],
                'total_questions' => 10,
                'time_limit' => null,
                'shuffle_questions' => true,
                'show_results_immediately' => false,
                'max_attempts' => 1,
                'scoring' => [
                    'mcq_weight' => 1.0,
                    'fill_blank_weight' => 2.0,
                    'code_weight' => 3.0
                ]
            ],
            'is_public' => $this->faker->boolean(20)
        ];
    }
}
