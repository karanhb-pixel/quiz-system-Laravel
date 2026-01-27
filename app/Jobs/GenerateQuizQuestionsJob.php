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

    /**
     * Create a new job instance.
     */
    public function __construct($quizId, $topic, $difficulty, $numQuestions, $questionType, $categoryId)
    {
        $this->quizId = $quizId;
        $this->topic = $topic;
        $this->difficulty = $difficulty;
        $this->numQuestions = $numQuestions;
        $this->questionType = $questionType;
        $this->categoryId = $categoryId;
    }

    /**
     * Execute the job.
     */
    public function handle(QuestionBankService $questionBankService): void
    {
        $quiz = Quiz::find($this->quizId);
        if (!$quiz) return;

        $quiz->update(['generation_status' => 'processing']);
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

            if (empty($questions)) {
                throw new \Exception("AI returned no questions. This often happens due to API rate limits or filtering.");
            }

            $quiz->update(['generation_status' => 'completed']);
            Log::info("Successfully generated " . count($questions) . " questions for Quiz ID: {$this->quizId}");

            // Dispatch real-time event for the browser
            \App\Events\QuizGenerationCompleted::dispatch($this->quizId);
            
        } catch (\Exception $e) {
            $quiz->update([
                'generation_status' => 'failed',
                'generation_error' => $e->getMessage()
            ]);
            Log::error("Failed to generate questions for Quiz ID: {$this->quizId} - " . $e->getMessage());
            throw $e; // This will trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $quiz = Quiz::find($this->quizId);
        if ($quiz) {
            $quiz->update([
                'generation_status' => 'failed',
                'generation_error' => "Final failure after retries: " . $exception->getMessage()
            ]);
        }
        Log::error("Job permanently failed for Quiz ID: {$this->quizId} - " . $exception->getMessage());
    }
}
