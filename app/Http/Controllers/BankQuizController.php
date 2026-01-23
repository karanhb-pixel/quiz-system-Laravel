<?php

namespace App\Http\Controllers;

use App\Models\BankQuestionResponse;
use App\Models\BankQuizAttempt;
use App\Models\QuestionBank;
use App\Services\GeminiService;
use Illuminate\Http\Request;

class BankQuizController extends Controller
{
    public function start(QuestionBank $bank)
    {
        // Get random questions from bank
        $questions = $this->getRandomQuestions($bank);

        // Create attempt record
        $attempt = BankQuizAttempt::create([
            'user_id' => auth()->id(),
            'question_bank_id' => $bank->id,
            'total_questions' => count($questions),
            'total_points' => collect($questions)->sum('points'),
            'points_earned' => 0,
            'score_percentage' => 0
        ]);

        // Store questions in session for this attempt
        session(['bank_quiz_' . $attempt->id => $questions]);

        return view('bank-quiz.attempt', compact('bank', 'attempt', 'questions'));
    }

    public function submit(Request $request, BankQuizAttempt $attempt)
    {
        // Prevent double submission
        if ($attempt->is_completed) {
            return redirect()->route('bank-quiz.result', $attempt);
        }

        $answers = $request->input('answers', []);
        $questions = session('bank_quiz_' . $attempt->id, []);

        $gemini = new GeminiService();
        $totalEarnedPoints = 0;

        foreach ($questions as $questionData) {
            $questionId = $questionData['id'];
            $questionType = $questionData['type'];
            $userAnswer = $answers[$questionId] ?? '';

            $evaluation = $this->evaluateAnswer($gemini, $questionData, $userAnswer);

            // Create response record
            BankQuestionResponse::create([
                'bank_quiz_attempt_id' => $attempt->id,
                'question_type' => $questionType,
                'question_id' => $questionId,
                'user_answer' => $userAnswer,
                'is_correct' => $evaluation['is_correct'],
                'points_earned' => $evaluation['points_earned'],
                'ai_feedback' => $evaluation['feedback'],
                'ai_confidence' => $evaluation['confidence']
            ]);

            $totalEarnedPoints += $evaluation['points_earned'];
        }

        // Update attempt with final score
        $scorePercentage = ($totalEarnedPoints / $attempt->total_points) * 100;

        $attempt->update([
            'points_earned' => $totalEarnedPoints,
            'score_percentage' => round($scorePercentage, 2),
            'completed_at' => now()
        ]);

        // Clear session
        session()->forget('bank_quiz_' . $attempt->id);

        return redirect()->route('bank-quiz.result', $attempt);
    }

    public function result(BankQuizAttempt $attempt)
    {
        // Ensure user owns this attempt
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }

        $attempt->load(['responses.question', 'questionBank']);

        return view('bank-quiz.result', compact('attempt'));
    }

    public function myAttempts()
    {
        $attempts = BankQuizAttempt::with(['questionBank'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('bank-quiz.my-attempts', compact('attempts'));
    }

    /**
     * Get random questions from bank (25/25/50 ratio)
     */
    private function getRandomQuestions(QuestionBank $bank)
    {
        $questions = collect();

        // Get question counts
        $mcqCount = $bank->mcqQuestions()->count();
        $fillBlankCount = $bank->fillBlankQuestions()->count();
        $codeCount = $bank->codeQuestions()->count();

        $totalQuestions = $bank->questions_per_quiz;

        // Calculate distribution (25% MCQ, 25% Fill-blank, 50% Code)
        $mcqNeeded = min($mcqCount, ceil($totalQuestions * 0.25));
        $fillBlankNeeded = min($fillBlankCount, ceil($totalQuestions * 0.25));
        $codeNeeded = $totalQuestions - $mcqNeeded - $fillBlankNeeded;

        // Adjust if we don't have enough questions
        if ($codeNeeded > $codeCount) {
            $codeNeeded = $codeCount;
            $remaining = $totalQuestions - $codeNeeded;
            $mcqNeeded = min($mcqCount, ceil($remaining * 0.5));
            $fillBlankNeeded = $remaining - $mcqNeeded;
        }

        // Get random questions
        if ($mcqNeeded > 0) {
            $mcqQuestions = $bank->mcqQuestions()->inRandomOrder()->take($mcqNeeded)->get()
                ->map(fn($q) => ['id' => $q->id, 'type' => 'mcq', 'model' => $q, 'points' => $q->points]);
            $questions = $questions->merge($mcqQuestions);
        }

        if ($fillBlankNeeded > 0) {
            $fillBlankQuestions = $bank->fillBlankQuestions()->inRandomOrder()->take($fillBlankNeeded)->get()
                ->map(fn($q) => ['id' => $q->id, 'type' => 'fill_blank', 'model' => $q, 'points' => $q->points]);
            $questions = $questions->merge($fillBlankQuestions);
        }

        if ($codeNeeded > 0) {
            $codeQuestions = $bank->codeQuestions()->inRandomOrder()->take($codeNeeded)->get()
                ->map(fn($q) => ['id' => $q->id, 'type' => 'code', 'model' => $q, 'points' => $q->points]);
            $questions = $questions->merge($codeQuestions);
        }

        return $questions->shuffle();
    }

    /**
     * Evaluate user answer based on question type
     */
    private function evaluateAnswer(GeminiService $gemini, array $questionData, string $userAnswer): array
    {
        $question = $questionData['model'];
        $maxPoints = $questionData['points'];

        switch ($questionData['type']) {
            case 'mcq':
                $isCorrect = strtolower($userAnswer) === $question->correct_answer;
                return [
                    'is_correct' => $isCorrect,
                    'points_earned' => $isCorrect ? $maxPoints : 0,
                    'feedback' => $isCorrect ? 'Correct!' : 'Incorrect. The correct answer is ' . strtoupper($question->correct_answer),
                    'confidence' => 1.0
                ];

            case 'fill_blank':
                $evaluation = $gemini->evaluateFillBlank(
                    $question->question_text,
                    $userAnswer,
                    $question->expected_answer,
                    $question->evaluation_hints,
                    $question->case_sensitive
                );

                return [
                    'is_correct' => $evaluation['is_correct'],
                    'points_earned' => $evaluation['score'] * $maxPoints,
                    'feedback' => $evaluation['feedback'],
                    'confidence' => $evaluation['confidence']
                ];

            case 'code':
                $evaluation = $gemini->evaluateCode(
                    $question->question_text,
                    $userAnswer,
                    $question->expected_code,
                    $question->evaluation_criteria,
                    $question->language
                );

                return [
                    'is_correct' => $evaluation['is_correct'],
                    'points_earned' => $evaluation['score'] * $maxPoints,
                    'feedback' => $evaluation['feedback'],
                    'confidence' => $evaluation['confidence']
                ];

            default:
                return [
                    'is_correct' => false,
                    'points_earned' => 0,
                    'feedback' => 'Unknown question type',
                    'confidence' => 0.0
                ];
        }
    }
}