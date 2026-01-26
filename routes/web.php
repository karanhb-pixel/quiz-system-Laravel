<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionBankController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;



    // TEMPORARY: Remove this after one successful use!
Route::get('/seed-database-now', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        return "Database Seeded Successfully! You can now log in.";
    } catch (\Exception $e) {
        return "Error seeding database: " . $e->getMessage();
    }
});

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
    
    Route::get('/quiz/result/{result}', [QuizController::class, 'showResult'])
        ->name('quizzes.result');
    Route::post('/quiz/result/{result}/evaluate', [QuizController::class, 'evaluate'])
        ->name('quizzes.evaluate');

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
});

Route::middleware(['auth', 'admin'])->group(function () {
    // Page to see all requests
    Route::get('/admin/requests', [UserController::class, 'requests'])->name('admin.requests');
    // Action to approve
    Route::post('/admin/approve/{user}', [UserController::class, 'approve'])->name('admin.approve');
    // Action to reject
    Route::post('/admin/reject/{user}', [UserController::class, 'reject'])->name('admin.reject');
});

// Question Bank Routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/question-bank', [QuestionBankController::class, 'showGenerationForm'])->name('question-bank.form');
    Route::post('/admin/question-bank/generate', [QuestionBankController::class, 'generateQuestions'])->name('question-bank.generate');
});

Route::middleware('auth')->group(function () {
    Route::get('/question-bank/{topic}', [QuestionBankController::class, 'getQuestionsByTopic'])->name('question-bank.topic');
});

require __DIR__.'/auth.php';
