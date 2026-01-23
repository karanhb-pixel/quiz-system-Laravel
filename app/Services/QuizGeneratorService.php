<?php

namespace App\Services;

use App\Models\QuestionBank;
use App\Models\McqBankQuestion;
use App\Models\FillBlankBankQuestion;
use App\Models\CodeBankQuestion;

class QuizGeneratorService
{
    /**
     * Generate quiz with advanced selection logic
     */
    public function generateQuiz(QuestionBank $bank, array $config = [])
    {
        $defaultConfig = [
            'total_questions' => $bank->questions_per_quiz,
            'distribution' => [
                'mcq' => 25,
                'fill_blank' => 25,
                'code' => 50
            ],
            'difficulty_balance' => true,
            'topic_balance' => true,
            'exclude_recent' => true,
            'user_history' => null
        ];

        $config = array_merge($defaultConfig, $config);

        $questions = collect();

        // Calculate question counts
        $totalQuestions = $config['total_questions'];
        $mcqCount = ceil($totalQuestions * ($config['distribution']['mcq'] / 100));
        $fillBlankCount = ceil($totalQuestions * ($config['distribution']['fill_blank'] / 100));
        $codeCount = $totalQuestions - $mcqCount - $fillBlankCount;

        // Adjust for available questions
        $availableMcq = $bank->mcqQuestions()->count();
        $availableFillBlank = $bank->fillBlankQuestions()->count();
        $availableCode = $bank->codeQuestions()->count();

        $mcqCount = min($mcqCount, $availableMcq);
        $fillBlankCount = min($fillBlankCount, $availableFillBlank);
        $codeCount = min($codeCount, $availableCode);

        // Redistribute if needed
        $totalSelected = $mcqCount + $fillBlankCount + $codeCount;
        if ($totalSelected < $totalQuestions) {
            $remaining = $totalQuestions - $totalSelected;
            if ($availableCode > $codeCount) {
                $codeCount += min($remaining, $availableCode - $codeCount);
            } elseif ($availableMcq > $mcqCount) {
                $mcqCount += min($remaining, $availableMcq - $mcqCount);
            } elseif ($availableFillBlank > $fillBlankCount) {
                $fillBlankCount += min($remaining, $availableFillBlank - $fillBlankCount);
            }
        }

        // Select questions with advanced logic
        if ($mcqCount > 0) {
            $mcqQuestions = $this->selectQuestionsAdvanced(
                $bank->mcqQuestions(),
                $mcqCount,
                $config,
                'mcq'
            );
            $questions = $questions->merge($mcqQuestions);
        }

        if ($fillBlankCount > 0) {
            $fillBlankQuestions = $this->selectQuestionsAdvanced(
                $bank->fillBlankQuestions(),
                $fillBlankCount,
                $config,
                'fill_blank'
            );
            $questions = $questions->merge($fillBlankQuestions);
        }

        if ($codeCount > 0) {
            $codeQuestions = $this->selectQuestionsAdvanced(
                $bank->codeQuestions(),
                $codeCount,
                $config,
                'code'
            );
            $questions = $questions->merge($codeQuestions);
        }

        return $questions->shuffle();
    }

    /**
     * Advanced question selection with balancing
     */
    private function selectQuestionsAdvanced($query, int $count, array $config, string $type)
    {
        $questions = collect();

        if ($config['difficulty_balance']) {
            // Balance by difficulty (if we add difficulty field later)
            $questions = $query->inRandomOrder()->take($count)->get();
        } else {
            $questions = $query->inRandomOrder()->take($count)->get();
        }

        if ($config['exclude_recent'] && $config['user_history']) {
            // Exclude recently answered questions
            $recentQuestionIds = $config['user_history']->pluck('question_id')->toArray();
            if (!empty($recentQuestionIds)) {
                $questions = $questions->filter(function ($question) use ($recentQuestionIds) {
                    return !in_array($question->id, $recentQuestionIds);
                });

                // Fill remaining slots if needed
                if ($questions->count() < $count) {
                    $needed = $count - $questions->count();
                    $additional = $query->whereNotIn('id', $recentQuestionIds)
                        ->inRandomOrder()
                        ->take($needed)
                        ->get();
                    $questions = $questions->merge($additional);
                }
            }
        }

        return $questions->map(function ($question) use ($type) {
            return [
                'id' => $question->id,
                'type' => $type,
                'model' => $question,
                'points' => $question->points,
                'difficulty' => $question->difficulty ?? null
            ];
        });
    }

    /**
     * Generate quiz statistics
     */
    public function generateQuizStats(QuestionBank $bank)
    {
        return [
            'total_questions' => $bank->total_questions,
            'question_breakdown' => [
                'mcq' => $bank->mcqQuestions()->count(),
                'fill_blank' => $bank->fillBlankQuestions()->count(),
                'code' => $bank->codeQuestions()->count()
            ],
            'average_difficulty' => $this->calculateAverageDifficulty($bank),
            'topic_coverage' => $this->analyzeTopicCoverage($bank),
            'recommended_configs' => $this->generateRecommendedConfigs($bank)
        ];
    }

    private function calculateAverageDifficulty(QuestionBank $bank)
    {
        // Placeholder for difficulty calculation
        return 'medium';
    }

    private function analyzeTopicCoverage(QuestionBank $bank)
    {
        // Placeholder for topic analysis
        return ['general' => 100];
    }

    private function generateRecommendedConfigs(QuestionBank $bank)
    {
        $total = $bank->total_questions;
        if ($total < 5) {
            return [['total_questions' => $total, 'distribution' => ['mcq' => 100, 'fill_blank' => 0, 'code' => 0]]];
        }

        return [
            ['total_questions' => min(10, $total), 'distribution' => ['mcq' => 40, 'fill_blank' => 30, 'code' => 30]],
            ['total_questions' => min(15, $total), 'distribution' => ['mcq' => 30, 'fill_blank' => 25, 'code' => 45]],
            ['total_questions' => min(20, $total), 'distribution' => ['mcq' => 25, 'fill_blank' => 25, 'code' => 50]]
        ];
    }

    /**
     * Generate quiz from template
     */
    public function generateFromTemplate($template, $user)
    {
        $questions = $this->generateQuiz($template->questionBank, $template->config);
        
        // Create attempt
        $attempt = \App\Models\BankQuizAttempt::create([
            'user_id' => $user->id,
            'question_bank_id' => $template->questionBank->id,
            'total_questions' => count($questions),
            'total_points' => collect($questions)->sum('points'),
            'points_earned' => 0,
            'score_percentage' => 0
        ]);

        // Store template reference
        $quiz = \App\Models\QuizFromTemplate::create([
            'quiz_template_id' => $template->id,
            'bank_quiz_attempt_id' => $attempt->id,
            'selected_questions' => $questions->pluck('id')->toArray()
        ]);

        // Increment usage count
        $template->increment('usage_count');

        return $quiz;
    }
}