<?php

use App\Http\Controllers\AiQuestionController;
use App\Http\Controllers\BankAnalyticsController;
use App\Http\Controllers\BankQuestionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionBankController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuizTemplateController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;



    Route::get('/', [UserController::class, 'dashboard'])->name('dashboard');
    
    // category filter List
    Route::get('/category/{category:slug}/quizzes', [QuizController::class, 'showByCategory'])
        ->name('quizzes.category');

    // Redirect old category ID URLs to slug URLs
    Route::get('/category/{id}/quizzes', function ($id) {
        $category = \App\Models\Category::findOrFail($id);
        return redirect()->route('quizzes.category', $category->slug);
    });
    
    // Only Guest/public Routes for Category
    Route::resource('categories', CategoryController::class)
        ->only(['index','show']);
    
    // only Guest/public routes for Quizzes
    Route::resource('quizzes', QuizController::class)
        ->only(['index','show']);

    // AI Health Check Route (public)
    Route::get('/ai-health-check', function () {
        try {
            $geminiConfig = config('gemini');
            $isEnabled = $geminiConfig['generation']['enabled'] ?? false;
            $hasApiKey = !empty($geminiConfig['api_key']);

            return response()->json([
                'status' => 'ok',
                'ai_generation_enabled' => $isEnabled,
                'api_key_configured' => $hasApiKey,
                'rate_limiting_enabled' => $geminiConfig['rate_limiting']['enabled'] ?? false,
                'cache_enabled' => $geminiConfig['cache']['enabled'] ?? false,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    })->name('ai-health-check');


// Dashboard route for authenticated users
Route::middleware('auth')->group(function () {
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Attemt and Submit quiz
    Route::get('/quiz/{quiz:slug}/attempt', [QuizController::class, 'attempt'])
        ->name('quizzes.attempt');
    Route::post('/quiz/{quiz:slug}/submit', [QuizController::class, 'submit'])
        ->name('quizzes.submit');

    // Redirect old quiz ID URLs to slug URLs
    Route::get('/quiz/{id}/attempt', function ($id) {
        $quiz = \App\Models\Quiz::findOrFail($id);
        return redirect()->route('quizzes.attempt', $quiz->slug);
    });

    Route::post('/quiz/{id}/submit', function ($id) {
        $quiz = \App\Models\Quiz::findOrFail($id);
        return redirect()->route('quizzes.submit', $quiz->slug);
    });

    Route::get('/{user:username}/attemptedQuiz',[UserController::class,'userAttemptedQuiz'])
        ->name('userAttemptedQuiz');

    // All Routes for Category
    Route::resource('categories', CategoryController::class)
        ->except(['index','show']);

    // all routes for Quizzes
    Route::resource('quizzes', QuizController::class)
        ->except(['index','show']);

    // all routes for questions
    Route::resource('questions', QuestionController::class);

    // Question Bank Management
    Route::resource('question-banks', QuestionBankController::class);

    // Bank Questions (nested routes)
    Route::get('/question-banks/{bank}/questions/create/{type}', [BankQuestionController::class, 'create'])
        ->name('bank-questions.create');
    Route::post('/question-banks/{bank}/questions', [BankQuestionController::class, 'store'])
        ->name('bank-questions.store');
    Route::get('/question-banks/{bank}/questions/{question}/edit/{type}', [BankQuestionController::class, 'edit'])
        ->name('bank-questions.edit');
    Route::put('/question-banks/{bank}/questions/{question}/{type}', [BankQuestionController::class, 'update'])
        ->name('bank-questions.update');
    Route::delete('/question-banks/{bank}/questions/{question}/{type}', [BankQuestionController::class, 'destroy'])
        ->name('bank-questions.destroy');

    // Bank Quiz Routes
    Route::get('/question-banks/{bank}/start', [App\Http\Controllers\BankQuizController::class, 'start'])
        ->name('bank-quiz.start');
    Route::post('/bank-quiz/{attempt}/submit', [App\Http\Controllers\BankQuizController::class, 'submit'])
        ->name('bank-quiz.submit');
    Route::get('/bank-quiz/{attempt}/result', [App\Http\Controllers\BankQuizController::class, 'result'])
        ->name('bank-quiz.result');
    Route::get('/my-bank-attempts', [App\Http\Controllers\BankQuizController::class, 'myAttempts'])
        ->name('bank-quiz.my-attempts');

    // Bank Analytics Routes
    Route::get('/question-banks/{bank}/analytics', [App\Http\Controllers\BankAnalyticsController::class, 'show'])
        ->name('bank-analytics.show');

    // AI Question Generation Routes
    Route::get('/question-banks/{bank}/ai-questions/create', [AiQuestionController::class, 'create'])
        ->name('ai-questions.create');
    Route::post('/question-banks/{bank}/ai-questions/generate', [AiQuestionController::class, 'generate'])
        ->name('ai-questions.generate');
    Route::post('/question-banks/{bank}/ai-questions', [AiQuestionController::class, 'store'])
        ->name('ai-questions.store');
    Route::post('/question-banks/{bank}/ai-questions/bulk-generate', [AiQuestionController::class, 'bulkGenerate'])
        ->name('ai-questions.bulk-generate');
    Route::get('/ai-questions/metrics', [AiQuestionController::class, 'metrics'])
        ->name('ai-questions.metrics');

    // Quiz Template Routes
    Route::resource('quiz-templates', QuizTemplateController::class);
    Route::get('/quiz-templates/{template}/take', [QuizTemplateController::class, 'take'])
        ->name('quiz-templates.take');
});

Route::middleware(['auth', 'admin'])->group(function () {
    // Page to see all requests
    Route::get('/admin/requests', [UserController::class, 'requests'])->name('admin.requests');
    // Action to approve
    Route::post('/admin/approve/{user}', [UserController::class, 'approve'])->name('admin.approve');
});

require __DIR__.'/auth.php';
