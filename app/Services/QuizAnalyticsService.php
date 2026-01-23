<?php

namespace App\Services;

use App\Models\BankQuizAttempt;
use App\Models\QuestionBank;
use Carbon\Carbon;

class QuizAnalyticsService
{
    /**
     * Generate comprehensive analytics for a question bank
     */
    public function generateAnalytics(QuestionBank $bank, $period = 'all')
    {
        $query = BankQuizAttempt::where('question_bank_id', $bank->id);

        if ($period !== 'all') {
            $query->where('created_at', '>=', Carbon::now()->subDays($period));
        }

        $attempts = $query->with('responses')->get();

        return [
            'overview' => $this->calculateOverviewMetrics($attempts),
            'performance' => $this->calculatePerformanceMetrics($attempts),
            'question_analysis' => $this->analyzeQuestionPerformance($attempts),
            'trends' => $this->calculateTrends($attempts),
            'recommendations' => $this->generateRecommendations($attempts, $bank)
        ];
    }

    private function calculateOverviewMetrics($attempts)
    {
        if ($attempts->isEmpty()) {
            return [
                'total_attempts' => 0,
                'average_score' => 0,
                'completion_rate' => 0,
                'average_time' => 0
            ];
        }

        $completed = $attempts->where('is_completed', true);

        return [
            'total_attempts' => $attempts->count(),
            'completed_attempts' => $completed->count(),
            'average_score' => $completed->avg('score_percentage'),
            'completion_rate' => $attempts->count() > 0 ? ($completed->count() / $attempts->count()) * 100 : 0,
            'average_time' => $completed->avg(function ($attempt) {
                return $attempt->created_at->diffInMinutes($attempt->completed_at ?? $attempt->created_at);
            })
        ];
    }

    private function calculatePerformanceMetrics($attempts)
    {
        $completed = $attempts->where('is_completed', true);

        if ($completed->isEmpty()) {
            return ['score_distribution' => [], 'performance_bands' => []];
        }

        // Score distribution
        $scores = $completed->pluck('score_percentage')->sort();
        $distribution = [
            '0-20' => $scores->filter(fn($s) => $s < 20)->count(),
            '20-40' => $scores->filter(fn($s) => $s >= 20 && $s < 40)->count(),
            '40-60' => $scores->filter(fn($s) => $s >= 40 && $s < 60)->count(),
            '60-80' => $scores->filter(fn($s) => $s >= 60 && $s < 80)->count(),
            '80-100' => $scores->filter(fn($s) => $s >= 80)->count()
        ];

        // Performance bands
        $bands = [
            'excellent' => $scores->filter(fn($s) => $s >= 90)->count(),
            'good' => $scores->filter(fn($s) => $s >= 80 && $s < 90)->count(),
            'average' => $scores->filter(fn($s) => $s >= 60 && $s < 80)->count(),
            'needs_improvement' => $scores->filter(fn($s) => $s < 60)->count()
        ];

        return [
            'score_distribution' => $distribution,
            'performance_bands' => $bands,
            'median_score' => $scores->median(),
            'score_variance' => $this->calculateVariance($scores)
        ];
    }

    private function analyzeQuestionPerformance($attempts)
    {
        $questionStats = [];

        foreach ($attempts as $attempt) {
            foreach ($attempt->responses as $response) {
                $key = $response->question_type . '_' . $response->question_id;

                if (!isset($questionStats[$key])) {
                    $questionStats[$key] = [
                        'question_id' => $response->question_id,
                        'question_type' => $response->question_type,
                        'total_attempts' => 0,
                        'correct_attempts' => 0,
                        'average_score' => 0,
                        'average_time' => 0
                    ];
                }

                $questionStats[$key]['total_attempts']++;
                if ($response->is_correct) {
                    $questionStats[$key]['correct_attempts']++;
                }
            }
        }

        // Calculate percentages and identify problematic questions
        foreach ($questionStats as &$stat) {
            $stat['correct_percentage'] = $stat['total_attempts'] > 0
                ? ($stat['correct_attempts'] / $stat['total_attempts']) * 100
                : 0;
        }

        return [
            'question_stats' => array_values($questionStats),
            'hardest_questions' => collect($questionStats)
                ->sortBy('correct_percentage')
                ->take(5)
                ->values(),
            'easiest_questions' => collect($questionStats)
                ->sortByDesc('correct_percentage')
                ->take(5)
                ->values()
        ];
    }

    private function calculateTrends($attempts)
    {
        $trends = [];

        // Group by date
        $byDate = $attempts->groupBy(function ($attempt) {
            return $attempt->created_at->format('Y-m-d');
        });

        foreach ($byDate as $date => $dayAttempts) {
            $completed = $dayAttempts->where('is_completed', true);
            $trends[] = [
                'date' => $date,
                'attempts' => $dayAttempts->count(),
                'completed' => $completed->count(),
                'average_score' => $completed->avg('score_percentage')
            ];
        }

        return array_slice(array_reverse($trends), 0, 30); // Last 30 days
    }

    private function generateRecommendations($attempts, $bank)
    {
        $recommendations = [];

        if ($attempts->isEmpty()) {
            return ['Add more questions to get meaningful analytics'];
        }

        $avgScore = $attempts->where('is_completed', true)->avg('score_percentage');

        if ($avgScore < 50) {
            $recommendations[] = 'Consider reviewing question difficulty - average score is below 50%';
        }

        if ($avgScore > 90) {
            $recommendations[] = 'Questions may be too easy - consider increasing difficulty';
        }

        $completionRate = $attempts->where('is_completed', true)->count() / $attempts->count();
        if ($completionRate < 0.7) {
            $recommendations[] = 'High dropout rate detected - consider shorter quizzes or time limits';
        }

        return $recommendations;
    }

    private function calculateVariance($scores)
    {
        if ($scores->isEmpty()) return 0;

        $mean = $scores->avg();
        $variance = $scores->map(fn($score) => pow($score - $mean, 2))->avg();

        return sqrt($variance); // Standard deviation
    }

    /**
     * Get basic bank statistics
     */
    public function getBankStatistics(QuestionBank $bank)
    {
        $attempts = BankQuizAttempt::where('question_bank_id', $bank->id)->get();

        if ($attempts->isEmpty()) {
            return [
                'total_attempts' => 0,
                'average_score' => 0,
                'highest_score' => null,
                'lowest_score' => null,
                'completion_rate' => 0
            ];
        }

        $completed = $attempts->where('completed_at', '!=', null);
        $scores = $completed->pluck('score_percentage');

        return [
            'total_attempts' => $attempts->count(),
            'average_score' => round($scores->avg()),
            'highest_score' => $scores->max(),
            'lowest_score' => $scores->min(),
            'completion_rate' => $attempts->count() > 0 ? round(($completed->count() / $attempts->count()) * 100) : 0
        ];
    }

    /**
     * Get question performance data
     */
    public function getQuestionPerformance(QuestionBank $bank)
    {
        $attempts = BankQuizAttempt::where('question_bank_id', $bank->id)
            ->with('responses')
            ->get();

        $questionStats = [];

        foreach ($attempts as $attempt) {
            foreach ($attempt->responses as $response) {
                $key = $response->question_type . '_' . $response->question_id;

                if (!isset($questionStats[$key])) {
                    $questionStats[$key] = [
                        'question_id' => $response->question_id,
                        'question_type' => $response->question_type,
                        'total_attempts' => 0,
                        'correct_attempts' => 0,
                        'correct_percentage' => 0
                    ];
                }

                $questionStats[$key]['total_attempts']++;
                if ($response->is_correct) {
                    $questionStats[$key]['correct_attempts']++;
                }
            }
        }

        // Calculate percentages
        foreach ($questionStats as &$stat) {
            $stat['correct_percentage'] = $stat['total_attempts'] > 0
                ? round(($stat['correct_attempts'] / $stat['total_attempts']) * 100)
                : 0;
        }

        return array_values($questionStats);
    }

    /**
     * Get score trend data
     */
    public function getScoreTrend(QuestionBank $bank, $days = 30)
    {
        $attempts = BankQuizAttempt::where('question_bank_id', $bank->id)
            ->where('completed_at', '!=', null)
            ->where('completed_at', '>=', now()->subDays($days))
            ->orderBy('completed_at')
            ->get()
            ->groupBy(function ($attempt) {
                return $attempt->completed_at->format('Y-m-d');
            });

        $trends = [];
        foreach ($attempts as $date => $dayAttempts) {
            $trends[] = [
                'date' => $date,
                'score' => round($dayAttempts->avg('score_percentage')),
                'attempts' => $dayAttempts->count()
            ];
        }

        return $trends;
    }

    /**
     * Get difficulty distribution
     */
    public function getDifficultyDistribution(QuestionBank $bank)
    {
        $mcqCount = $bank->mcqQuestions()->count();
        $fillBlankCount = $bank->fillBlankQuestions()->count();
        $codeCount = $bank->codeQuestions()->count();

        // For now, return simple counts. In a real implementation,
        // you'd check difficulty levels on each question
        return [
            'easy' => max(1, intval(($mcqCount + $fillBlankCount + $codeCount) * 0.4)),
            'medium' => max(1, intval(($mcqCount + $fillBlankCount + $codeCount) * 0.4)),
            'hard' => max(1, intval(($mcqCount + $fillBlankCount + $codeCount) * 0.2))
        ];
    }
}