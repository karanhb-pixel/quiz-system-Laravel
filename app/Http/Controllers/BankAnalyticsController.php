<?php

namespace App\Http\Controllers;

use App\Models\QuestionBank;
use App\Services\QuizAnalyticsService;
use Illuminate\Http\Request;

class BankAnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(QuizAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function show(QuestionBank $bank)
    {
        $this->authorize('update', $bank);

        $stats = $this->analyticsService->getBankStatistics($bank);
        $questionPerformance = $this->analyticsService->getQuestionPerformance($bank);
        $recentAttempts = $bank->attempts()
            ->with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('bank-analytics.show', compact('bank', 'stats', 'questionPerformance', 'recentAttempts'));
    }
}