# Phase 5: AI Performance - Background Queues Implementation

## Overview

Currently, when users generate AI questions, they must wait 5-10 seconds for Gemini to respond. This creates a poor user experience. Background queues solve this by:

- Returning instant response to the user
- Processing AI requests in the background
- Notifying users when generation is complete

---

## Benefits

- ✅ **Instant page response** - No more waiting for AI
- ✅ **Better UX** - Users can continue working while AI generates
- ✅ **Scalability** - Handle multiple AI requests simultaneously
- ✅ **Reliability** - Failed jobs can retry automatically

---

## Phase 5.1: Setup Queue Infrastructure

### Step 5.1.1: Configure Queue Driver

**Current State:**
Your `.env` has `QUEUE_CONNECTION=database` (or `sync`)

**What we'll do:**
Keep using `database` driver (simple, no extra services needed)

**Action:**

1. Open `.env`
2. Verify or add:

```env
QUEUE_CONNECTION=database
```

**Checkpoint:**

- `.env` has `QUEUE_CONNECTION=database`

---

### Step 5.1.2: Create Jobs Table Migration

**Run this command:**

```bash
php artisan queue:table
```

**What this does:**
Creates a migration for the `jobs` table where queued tasks are stored.

**Checkpoint:**

- New migration file created in `database/migrations/`
- File name contains `create_jobs_table`

---

### Step 5.1.3: Run the Migration

**Run this command:**

```bash
php artisan migrate
```

**What this does:**
Creates the `jobs` table in your database.

**Checkpoint:**

- Migration runs successfully
- `jobs` table exists in database

---

## Phase 5.2: Create Question Generation Job

### Step 5.2.1: Generate Job Class

**Run this command:**

```bash
php artisan make:job GenerateQuizQuestionsJob
```

**What this does:**
Creates `app/Jobs/GenerateQuizQuestionsJob.php`

**Checkpoint:**

- File `app/Jobs/GenerateQuizQuestionsJob.php` exists

---

### Step 5.2.2: Implement Job Logic

**Edit the job file:**

```php
<?php

namespace App\Jobs;

use App\Models\Quiz;
use App\QuestionBank\QuestionBankService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateQuizQuestionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120; // 2 minutes max
    public $tries = 2; // Retry once if it fails

    protected $quizId;
    protected $topic;
    protected $difficulty;
    protected $numQuestions;
    protected $questionType;
    protected $categoryId;

    public function __construct($quizId, $topic, $difficulty, $numQuestions, $questionType, $categoryId)
    {
        $this->quizId = $quizId;
        $this->topic = $topic;
        $this->difficulty = $difficulty;
        $this->numQuestions = $numQuestions;
        $this->questionType = $questionType;
        $this->categoryId = $categoryId;
    }

    public function handle(QuestionBankService $questionBankService)
    {
        Log::info("Starting background question generation for Quiz ID: {$this->quizId}");

        try {
            $questions = $questionBankService->generateAndStoreQuestions(
                $this->topic,
                $this->difficulty,
                $this->numQuestions,
                $this->questionType,
                $this->categoryId,
                $this->quizId
            );

            Log::info("Successfully generated " . count($questions) . " questions for Quiz ID: {$this->quizId}");

            // Optional: Send notification to user here

        } catch (\Exception $e) {
            Log::error("Failed to generate questions for Quiz ID: {$this->quizId} - " . $e->getMessage());
            throw $e; // This will trigger retry
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("Job permanently failed for Quiz ID: {$this->quizId} - " . $exception->getMessage());

        // Optional: Notify user of failure
    }
}
```

**Checkpoint:**

- Job file has all the code above
- No syntax errors

---

## Phase 5.3: Update Controller to Use Queue

### Step 5.3.1: Modify QuestionBankController

**Current code (synchronous):**

```php
$questions = $this->questionBankService->generateAndStoreQuestions(...);
```

**New code (asynchronous):**

```php
use App\Jobs\GenerateQuizQuestionsJob;

// Dispatch to queue instead of running immediately
GenerateQuizQuestionsJob::dispatch(
    $quiz->id,
    $topic,
    $difficulty,
    $numQuestions,
    $questionType,
    $categoryId
);
```

**Full updated method:**

```php
public function generateQuestions(Request $request)
{
    $request->validate([
        'category_id' => 'required|exists:categories,id',
        'question_type' => 'required|string|in:mcq,fill_blank,code',
        'topic' => 'required|string|max:255',
        'difficulty' => 'required|string|in:easy,medium,hard',
        'num_questions' => 'integer|min:1|max:20',
        'instructions' => 'nullable|string|max:1000'
    ]);

    $categoryId = $request->input('category_id');
    $questionType = $request->input('question_type');
    $topic = $request->input('topic');
    $difficulty = $request->input('difficulty');
    $numQuestions = $request->input('num_questions', 10);
    $instructions = $request->input('instructions') ?: null;

    // Create quiz first
    $quiz = Quiz::create([
        'title' => $topic,
        'category_id' => $categoryId,
        'description' => $instructions,
        'slug' => Str::slug($topic),
        'user_id' => auth()->id(),
    ]);

    // Dispatch to background queue
    GenerateQuizQuestionsJob::dispatch(
        $quiz->id,
        $topic,
        $difficulty,
        $numQuestions,
        $questionType,
        $categoryId
    );

    // Return immediately
    return redirect()->route('quizzes.show', $quiz->slug)
                    ->with('success', "Quiz created! Questions are being generated in the background. Refresh in a few moments.");
}
```

**Checkpoint:**

- Controller imports the Job class
- `dispatch()` is used instead of direct service call
- User gets instant redirect

---

## Phase 5.4: Start Queue Worker

### Step 5.4.1: Run Queue Worker Locally

**For development, run:**

```bash
php artisan queue:work --tries=2 --timeout=120
```

**What this does:**

- Starts a worker that processes queued jobs
- Retries failed jobs once
- Times out after 2 minutes per job

**Keep this running in a separate terminal while developing**

**Checkpoint:**

- Command runs without errors
- Shows "Processing jobs from queue..."

---

### Step 5.4.2: Configure for Production (Railway)

**Railway automatically runs queue workers if you add this to your Procfile:**

Create `Procfile` in project root:

```
web: /start-container.sh
worker: php artisan queue:work --tries=2 --timeout=120 --sleep=3
```

**Or configure in Railway dashboard:**

- Add a new service
- Type: Worker
- Command: `php artisan queue:work --tries=2 --timeout=120`

**Checkpoint:**

- Procfile exists OR Railway worker service configured

---

## Phase 5.5: Testing

### Step 5.5.1: Test Locally

1. Start queue worker: `php artisan queue:work`
2. Generate questions via the form
3. You should be redirected INSTANTLY
4. Watch the queue worker terminal - you'll see it processing
5. Refresh the quiz page after a few seconds - questions appear!

**Checkpoint:**

- Instant redirect works
- Queue worker shows job processing
- Questions appear after refresh

---

### Step 5.5.2: Monitor Queue

**Check pending jobs:**

```bash
php artisan queue:monitor
```

**Check failed jobs:**

```bash
php artisan queue:failed
```

**Retry failed jobs:**

```bash
php artisan queue:retry all
```

**Checkpoint:**

- You know how to monitor the queue
- Failed jobs can be retried

---

## Phase 5.6: Optional Enhancements

### Add Progress Notifications (Advanced)

**Option 1: Polling**

- Add a "status" column to quizzes table
- Update it when job completes
- Frontend polls every 2 seconds to check status

**Option 2: Laravel Reverb (Real-time)**

- Use Laravel's WebSocket server
- Push notification when job completes
- Requires additional setup

**For now:** Keep it simple with the refresh message

---

## Verification Checklist

Before marking Phase 5 complete:

- [ ] `QUEUE_CONNECTION=database` in `.env`
- [ ] `jobs` table exists in database
- [ ] `GenerateQuizQuestionsJob.php` created
- [ ] Job has proper timeout and retry logic
- [ ] Controller dispatches job instead of calling service directly
- [ ] User gets instant feedback message
- [ ] Queue worker runs successfully
- [ ] Test generation works end-to-end
- [ ] Questions appear after background processing

---

## Troubleshooting

### "Jobs not processing"

- Is queue worker running? (`php artisan queue:work`)
- Check `jobs` table - are there pending jobs?
- Check logs: `storage/logs/laravel.log`

### "Job fails immediately"

- Check timeout (increase if needed)
- Check logs for error message
- Verify Gemini API key is set

### "Questions never appear"

- Check `failed_jobs` table
- Run `php artisan queue:retry all`
- Check if job completed but questions didn't save

---

## Next Steps

After Phase 5, you'll have:
✅ Non-blocking AI generation
✅ Better user experience
✅ Scalable architecture

**Ready for:**

- Infrastructure optimizations (FrankenPHP Worker Mode)
- Security (Rate Limiting)
- Advanced features (Real-time notifications)
