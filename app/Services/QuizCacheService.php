<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\QuestionBank;

class QuizCacheService
{
    public function getQuizStats($bankId)
    {
        return Cache::remember("quiz_stats_{$bankId}", 3600, function () use ($bankId) {
            $analytics = app(QuizAnalyticsService::class);
            return $analytics->generateAnalytics(\App\Models\QuestionBank::find($bankId));
        });
    }

    public function getQuestionBankStats($bankId)
    {
        return Cache::remember("bank_stats_{$bankId}", 1800, function () use ($bankId) {
            $generator = app(QuizGeneratorService::class);
            return $generator->generateQuizStats(\App\Models\QuestionBank::find($bankId));
        });
    }

    public function invalidateBankCache($bankId)
    {
        Cache::forget("quiz_stats_{$bankId}");
        Cache::forget("bank_stats_{$bankId}");
        Cache::forget("bank:{$bankId}:questions");
    }

    public function getTemplateStats($templateId)
    {
        return Cache::remember("template_stats_{$templateId}", 1800, function () use ($templateId) {
            $template = \App\Models\QuizTemplate::find($templateId);
            if ($template) {
                $generator = app(QuizGeneratorService::class);
                return $generator->generateQuizStats($template->questionBank);
            }
            return null;
        });
    }

    public function invalidateTemplateCache($templateId)
    {
        Cache::forget("template_stats_{$templateId}");
    }

    public function clearAllCache()
    {
        Cache::flush();
    }

    public function cacheBankQuestions(QuestionBank $bank)
    {
        $generator = app(QuizGeneratorService::class);
        $questions = $generator->generateQuiz($bank);
        
        Cache::put("bank:{$bank->id}:questions", $questions, now()->addMinutes(60));
    }

    public function getCachedQuestions(QuestionBank $bank, $count)
    {
        $cached = Cache::get("bank:{$bank->id}:questions");
        
        if (!$cached) {
            $this->cacheBankQuestions($bank);
            $cached = Cache::get("bank:{$bank->id}:questions");
        }

        return $cached->take($count);
    }
}