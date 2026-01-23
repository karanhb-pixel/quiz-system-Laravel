# Implementation Plan: Question Bank System with AI-Powered Grading

## Overview

Create a **NEW question bank system** alongside the existing quiz system:

### Existing System (Unchanged)
- **Quizzes**: Manual quiz creation with fixed questions
- **Questions**: MCQ questions (A/B/C/D format)
- **Results**: Quiz attempt tracking

### Phase 1: Core System (Current Focus)
- **Question Banks**: Container for question pools
- **Management UI**: Creating and managing banks
- **Step-by-step Implementation**: Schema → Models → CRUD → UI

### Later Phases
- **Random Quiz Generation**: System selects random questions from banks (25/25/50 ratio)
- **AI Grading**: Google Gemini API evaluates fill-blank and code answers
- **Scoring**: Flexible system (Weighted/Custom)

---

## User Review Required

> [!IMPORTANT]
> **API Key Required**: You'll need a free Google Gemini API key from [Google AI Studio](https://aistudio.google.com/app/apikey).

> [!IMPORTANT]
> **Separate System**: This creates a completely new question bank feature. Your existing quiz system remains 100% unchanged and will continue working exactly as it does now.

> [!CAUTION]
> **AI Grading**: AI evaluation provides ~85-95% accuracy. For high-stakes assessments, consider adding manual review capability.

---

## Proposed Changes

### Component 1: Database Schema (New Tables)

All new migrations - **zero changes** to existing tables.

---

#### [NEW] Migration: `create_question_banks_table.php`

```php
Schema::create('question_banks', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->foreignId('category_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Creator
    $table->integer('questions_per_quiz')->default(10); // How many random questions
    $table->timestamps();
});
```

**Purpose**: Container for question pools

---

#### [NEW] Migration: `create_mcq_bank_questions_table.php`

```php
Schema::create('mcq_bank_questions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('question_bank_id')->constrained()->onDelete('cascade');
    $table->text('question_text');
    $table->string('option_a');
    $table->string('option_b');
    $table->string('option_c');
    $table->string('option_d');
    $table->char('correct_answer', 1); // 'a', 'b', 'c', or 'd'
    $table->integer('points')->default(1);
    $table->timestamps();
});
```

**Purpose**: MCQ questions in banks (similar to existing Question model)

---

#### [NEW] Migration: `create_fill_blank_bank_questions_table.php`

```php
Schema::create('fill_blank_bank_questions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('question_bank_id')->constrained()->onDelete('cascade');
    $table->text('question_text');
    $table->text('expected_answer'); // Reference answer for AI
    $table->text('evaluation_hints')->nullable(); // Guidance for AI grading
    $table->boolean('case_sensitive')->default(false);
    $table->integer('points')->default(1);
    $table->timestamps();
});
```

**Purpose**: Fill-in-blank questions with AI evaluation criteria

---

#### [NEW] Migration: `create_code_bank_questions_table.php`

```php
Schema::create('code_bank_questions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('question_bank_id')->constrained()->onDelete('cascade');
    $table->text('question_text'); // e.g., "Write CSS for blue background, white text"
    $table->text('expected_code'); // Reference solution
    $table->text('evaluation_criteria')->nullable(); // What to check (syntax, specific properties, etc.)
    $table->string('language')->default('css'); // 'css', 'html', 'javascript', 'python', etc.
    $table->integer('points')->default(2); // Code questions worth more
    $table->timestamps();
});
```

**Purpose**: Code-based questions with language specification

---

#### [NEW] Migration: `create_bank_quiz_attempts_table.php`

```php
Schema::create('bank_quiz_attempts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('question_bank_id')->constrained()->onDelete('cascade');
    $table->integer('total_questions');
    $table->integer('total_points');
    $table->integer('points_earned');
    $table->decimal('score_percentage', 5, 2);
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
});
```

**Purpose**: Track user attempts on bank-generated quizzes

---

#### [NEW] Migration: `create_bank_question_responses_table.php`

```php
Schema::create('bank_question_responses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('bank_quiz_attempt_id')->constrained()->onDelete('cascade');
    $table->string('question_type'); // 'mcq', 'fill_blank', 'code'
    $table->unsignedBigInteger('question_id'); // Polymorphic reference
    $table->text('user_answer');
    $table->boolean('is_correct');
    $table->decimal('points_earned', 5, 2);
    $table->text('ai_feedback')->nullable(); // AI explanation
    $table->decimal('ai_confidence', 3, 2)->nullable(); // 0.00 - 1.00
    $table->timestamps();
    
    // Index for polymorphic relationship
    $table->index(['question_type', 'question_id']);
});
```

**Purpose**: Store individual answers with AI feedback

---

### Component 2: Backend - Models

#### [NEW] `app/Models/QuestionBank.php`

```php
class QuestionBank extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'category_id', 'user_id', 'questions_per_quiz'];
    
    // Relationships
    public function category() { return $this->belongsTo(Category::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function mcqQuestions() { return $this->hasMany(McqBankQuestion::class); }
    public function fillBlankQuestions() { return $this->hasMany(FillBlankBankQuestion::class); }
    public function codeQuestions() { return $this->hasMany(CodeBankQuestion::class); }
    
    // Get random questions for quiz generation
    public function getRandomQuestions($count = null)
    {
        $count = $count ?? $this->questions_per_quiz;
        // Implementation: Randomly select from all question types
    }
}
```

---

#### [NEW] `app/Models/McqBankQuestion.php`

Standard Eloquent model with relationship to `QuestionBank`

---

#### [NEW] `app/Models/FillBlankBankQuestion.php`

Standard Eloquent model with relationship to `QuestionBank`

---

#### [NEW] `app/Models/CodeBankQuestion.php`

Standard Eloquent model with relationship to `QuestionBank`

---

#### [NEW] `app/Models/BankQuizAttempt.php`

```php
class BankQuizAttempt extends Model
{
    protected $fillable = ['user_id', 'question_bank_id', 'total_questions', 
                          'total_points', 'points_earned', 'score_percentage', 'completed_at'];
    
    public function questionBank() { return $this->belongsTo(QuestionBank::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function responses() { return $this->hasMany(BankQuestionResponse::class); }
}
```

---

#### [NEW] `app/Models/BankQuestionResponse.php`

```php
class BankQuestionResponse extends Model
{
    protected $fillable = ['bank_quiz_attempt_id', 'question_type', 'question_id', 
                          'user_answer', 'is_correct', 'points_earned', 'ai_feedback', 'ai_confidence'];
    
    public function attempt() { return $this->belongsTo(BankQuizAttempt::class); }
    
    // Polymorphic relationship to get the actual question
    public function question()
    {
        return $this->morphTo(null, 'question_type', 'question_id');
    }
}
```

---

### Component 3: Backend - AI Service

#### [NEW] `app/Services/GeminiService.php`

```php
namespace App\Services;

use Gemini\Client;
use Gemini\Data\Content;
use Gemini\Enums\Role;

class GeminiService
{
    private Client $client;
    
    public function __construct()
    {
        $this->client = new Client(config('gemini.api_key'));
    }
    
    /**
     * Evaluate fill-in-blank answer
     */
    public function evaluateFillBlank($question, $userAnswer, $expectedAnswer, $hints = null)
    {
        $prompt = $this->buildFillBlankPrompt($question, $userAnswer, $expectedAnswer, $hints);
        return $this->evaluate($prompt);
    }
    
    /**
     * Evaluate code answer
     */
    public function evaluateCode($question, $userCode, $expectedCode, $criteria, $language)
    {
        $prompt = $this->buildCodePrompt($question, $userCode, $expectedCode, $criteria, $language);
        return $this->evaluate($prompt);
    }
    
    /**
     * Core evaluation method
     * Returns: ['is_correct' => bool, 'confidence' => float, 'feedback' => string]
     */
    private function evaluate($prompt)
    {
        try {
            $response = $this->client->geminiPro()->generateContent($prompt);
            return $this->parseResponse($response->text());
        } catch (\Exception $e) {
            // Fallback: Flag for manual review
            return [
                'is_correct' => false,
                'confidence' => 0.0,
                'feedback' => 'AI evaluation failed. Manual review required.'
            ];
        }
    }
    
    // Prompt builders and response parsing...
}
```

#### [MODIFY] [composer.json](file:///c:/Users/Admin/Herd/quiz-system/composer.json)

Add: `"google-gemini-php/client": "^0.8"`

#### [MODIFY] [.env](file:///c:/Users/Admin/Herd/quiz-system/.env)

Add: `GEMINI_API_KEY=your_api_key_here`

#### [NEW] `config/gemini.php`

API configuration file

---

---

### Component 4: Backend - Scoring System

#### Implementation of Scoring Logic
The system will support dynamic scoring for randomized quizzes:
- **Default Behavior**: Questions inherit 1 point each.
- **Weighted Mode**: 
  - MCQ: 1 point
  - Fill-in-blank: 2 points
  - Code: 3 points
- **Instructor Customization**: Instructors can override points per question during creation.

**Score Calculation**:
1. Sum of all possible points for selected random questions.
2. Sum of points earned based on AI evaluation and direct matches.
3. Final percentage = (Earned / Total) * 100.

---

### Component 5: Backend - Controllers

#### [NEW] `app/Http/Controllers/QuestionBankController.php`

CRUD operations for question banks:
- `index()` - List all banks (with question counts)
- `create()` - Show bank creation form
- `store()` - Save new bank
- `show($bank)` - View bank details + questions
- `edit($bank)` - Edit bank form
- `update($bank)` - Update bank
- `destroy($bank)` - Delete bank

---

#### [NEW] `app/Http/Controllers/BankQuestionController.php`

Manage questions in banks:
- `create($bank, $type)` - Create question form (MCQ/fill-blank/code)
- `store($bank, $type)` - Save question
- `edit($question, $type)` - Edit question form
- `update($question, $type)` - Update question
- `destroy($question, $type)` - Delete question

---

#### [NEW] `app/Http/Controllers/BankQuizController.php`

Random quiz generation and submission:

```php
public function start(QuestionBank $bank)
{
    // Generate random quiz from bank
    $questions = $bank->getRandomQuestions();
    
    // Create attempt record
    $attempt = BankQuizAttempt::create([...]);
    
    return view('bank-quiz.attempt', compact('bank', 'questions', 'attempt'));
}

public function submit(Request $request, BankQuizAttempt $attempt)
{
    $answers = $request->input('answers');
    $gemini = new GeminiService();
    
    foreach ($answers as $questionId => $answer) {
        // Determine question type
        // If MCQ: Direct comparison
        // If Fill-blank: $gemini->evaluateFillBlank(...)
        // If Code: $gemini->evaluateCode(...)
        
        // Store in bank_question_responses
    }
    
    // Calculate score, update attempt
    return view('bank-quiz.result', compact('attempt'));
}
```

---

### Component 5: Routes

#### [MODIFY] [web.php](file:///c:/Users/Admin/Herd/quiz-system/routes/web.php)

Add new routes (existing routes unchanged):

```php
Route::middleware('auth')->group(function () {
    // Question Bank Management
    Route::resource('question-banks', QuestionBankController::class);
    
    // Bank Questions (nested routes)
    Route::get('/question-banks/{bank}/questions/create/{type}', [BankQuestionController::class, 'create'])
        ->name('bank-questions.create');
    Route::post('/question-banks/{bank}/questions/{type}', [BankQuestionController::class, 'store'])
        ->name('bank-questions.store');
    // Edit/update/destroy routes...
    
    // Take Bank Quiz
    Route::get('/question-banks/{bank}/start', [BankQuizController::class, 'start'])
        ->name('bank-quiz.start');
    Route::post('/bank-quiz/{attempt}/submit', [BankQuizController::class, 'submit'])
        ->name('bank-quiz.submit');
    
    // View Attempts
    Route::get('/my-bank-attempts', [BankQuizController::class, 'myAttempts'])
        ->name('bank-quiz.my-attempts');
});
```

---

### Component 6: Frontend - Question Banks

#### [NEW] `resources/views/question-banks/index.blade.php`

List of question banks with:
- Bank name, description, category
- Question count (total, by type)
- "Take Quiz" button (for students)
- "Manage Bank" button (for creators)

---

#### [NEW] `resources/views/question-banks/create.blade.php`

Form to create bank:
- Name, description
- Category selection
- Questions per quiz (slider: 5-50)

---

#### [NEW] `resources/views/question-banks/show.blade.php`

Bank details page:
- List all questions (grouped by type)
- Add question buttons (MCQ / Fill-blank / Code)
- Edit/delete question actions

---

### Component 7: Frontend - Bank Questions

#### [NEW] `resources/views/bank-questions/create.blade.php`

Dynamic form based on question type:

**MCQ Type**:
- Question text
- Options A, B, C, D
- Correct answer dropdown
- Points

**Fill-blank Type**:
- Question text
- Expected answer
- Evaluation hints (optional)
- Case sensitive toggle
- Points

**Code Type**:
- Question text
- Language dropdown (CSS/HTML/JS/Python)
- Expected code (with syntax highlighting)
- Evaluation criteria
- Points

---

### Component 8: Frontend - Bank Quiz Attempt

#### [NEW] `resources/views/bank-quiz/attempt.blade.php`

Shows randomly generated quiz:
- Question counter (1/10, 2/10, etc.)
- Different input types:
  - **MCQ**: Radio buttons
  - **Fill-blank**: Text input
  - **Code**: Textarea with monospace font + syntax highlighting
- Submit quiz button

---

### Component 9: Frontend - Results

#### [NEW] `resources/views/bank-quiz/result.blade.php`

Detailed results page:
- Overall score (points earned / total points, percentage)
- Question-by-question breakdown:
  - Question text
  - Your answer
  - Correct answer / Expected answer
  - AI feedback (for fill-blank/code)
  - AI confidence badge
  - Color coding (green/red/yellow)

---

### Component 10: UI Integration

#### [MODIFY] [dashboard.blade.php](file:///c:/Users/Admin/Herd/quiz-system/resources/views/dashboard.blade.php)

Add new section:
```html
<div class="card">
    <h3>Question Banks</h3>
    <p>Take randomized quizzes from question pools</p>
    <a href="{{ route('question-banks.index') }}">Browse Question Banks</a>
</div>
```

---

## Verification Plan

### Automated Tests

#### [NEW] `tests/Feature/QuestionBankTest.php`

```php
test('can create question bank')
test('can add mcq question to bank')
test('can add fill-blank question to bank')
test('can add code question to bank')
test('random quiz generation works')
test('mcq answers graded correctly')
test('fill-blank uses AI grading')
test('code questions use AI grading')
test('existing quiz system still works') // ← Critical!
```

Run: `php artisan test --filter=QuestionBankTest`

---

### Manual Verification

1. **Install dependencies**
   ```bash
   composer require google-gemini-php/client
   php artisan migrate
   ```

2. **Create a question bank**
   - Navigate to Question Banks
   - Create new bank (e.g., "CSS Basics")
   - Add 3 MCQ questions
   - Add 2 fill-blank questions
   - Add 2 code questions (CSS)

3. **Take a random quiz**
   - Click "Start Quiz" on the bank
   - Verify random questions appear
   - Answer all questions
   - Submit

4. **Verify results**
   - Check overall score
   - Review AI feedback on fill-blank/code questions
   - Verify MCQ grading is correct

5. **Test existing system**
   - Go to original Quizzes section
   - Create/take old-style quiz
   - Verify it works exactly as before

---

## User Decisions ✅

1. **Question Bank Access**: All logged-in users can see all banks
2. **Question Distribution**: Specific ratio - **25% MCQ, 25% Fill-blank, 50% Code**
3. **Attempts Tracking**: No limit on attempts
4. **Leaderboard**: Not needed
