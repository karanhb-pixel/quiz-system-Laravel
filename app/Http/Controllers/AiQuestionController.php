<?php

namespace App\Http\Controllers;

use App\Models\QuestionBank;
use App\Models\McqBankQuestion;
use App\Models\FillBlankBankQuestion;
use App\Models\CodeBankQuestion;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AiQuestionController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Show AI question generation form
     */
    public function create(QuestionBank $bank)
    {
        $this->authorize('update', $bank);

        return view('ai-questions.create', compact('bank'));
    }

    /**
     * Generate AI question
     */
    public function generate(Request $request, QuestionBank $bank)
    {
        $this->authorize('update', $bank);

        $validated = $request->validate([
            'question_type' => 'required|in:mcq,fill_blank,code',
            'topic' => 'required|string|max:255',
            'difficulty' => 'required|in:easy,medium,hard',
            'language' => 'required_if:question_type,code|in:php,javascript,python,java,cpp',
            'additional_context' => 'nullable|string|max:1000'
        ]);

        try {
            $generatedQuestion = match($validated['question_type']) {
                'mcq' => $this->geminiService->generateMcqQuestion(
                    $validated['topic'],
                    $validated['difficulty'],
                    $validated['additional_context'] ?? null,
                    auth()->id()
                ),
                'fill_blank' => $this->geminiService->generateFillBlankQuestion(
                    $validated['topic'],
                    $validated['difficulty'],
                    $validated['additional_context'] ?? null,
                    auth()->id()
                ),
                'code' => $this->geminiService->generateCodeQuestion(
                    $validated['topic'],
                    $validated['difficulty'],
                    $validated['language'],
                    $validated['additional_context'] ?? null,
                    auth()->id()
                )
            };

            return response()->json([
                'success' => true,
                'question' => $generatedQuestion
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate question: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save generated AI question to bank
     */
    public function store(Request $request, QuestionBank $bank)
    {
        $this->authorize('update', $bank);

        $validated = $request->validate([
            'question_type' => 'required|in:mcq,fill_blank,code',
            'question_data' => 'required|array',
            'confirm_save' => 'required|boolean'
        ]);

        if (!$validated['confirm_save']) {
            return response()->json(['message' => 'Question not saved'], 200);
        }

        $questionData = $validated['question_data'];
        $questionData['question_bank_id'] = $bank->id;

        // Add AI metadata if present
        if (isset($questionData['generation_metadata'])) {
            $questionData['generated_by_ai'] = true;
            $questionData['needs_review'] = $questionData['needs_review'] ?? false;
            $questionData['quality_score'] = $questionData['quality_score'] ?? null;
        }

        try {
            $savedQuestion = match($validated['question_type']) {
                'mcq' => \App\Models\McqBankQuestion::create($questionData),
                'fill_blank' => \App\Models\FillBlankBankQuestion::create($questionData),
                'code' => \App\Models\CodeBankQuestion::create($questionData)
            };

            return response()->json([
                'success' => true,
                'message' => 'AI-generated question saved successfully!',
                'question_id' => $savedQuestion->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save question: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk generate questions
     */
    public function bulkGenerate(Request $request, QuestionBank $bank)
    {
        $this->authorize('update', $bank);

        $validated = $request->validate([
            'question_type' => 'required|in:mcq,fill_blank,code',
            'topic' => 'required|string|max:255',
            'difficulty' => 'required|in:easy,medium,hard',
            'count' => 'required|integer|min:1|max:10',
            'language' => 'required_if:question_type,code|in:php,javascript,python,java,cpp',
            'additional_context' => 'nullable|string|max:1000'
        ]);

        $generatedQuestions = [];
        $errors = [];

        for ($i = 0; $i < $validated['count']; $i++) {
            try {
                $question = match($validated['question_type']) {
                    'mcq' => $this->geminiService->generateMcqQuestion(
                        $validated['topic'],
                        $validated['difficulty'],
                        $validated['additional_context'] ?? null,
                        auth()->id()
                    ),
                    'fill_blank' => $this->geminiService->generateFillBlankQuestion(
                        $validated['topic'],
                        $validated['difficulty'],
                        $validated['additional_context'] ?? null,
                        auth()->id()
                    ),
                    'code' => $this->geminiService->generateCodeQuestion(
                        $validated['topic'],
                        $validated['difficulty'],
                        $validated['language'],
                        $validated['additional_context'] ?? null,
                        auth()->id()
                    )
                };

                $generatedQuestions[] = $question;

                // Add small delay to avoid rate limiting
                usleep(100000); // 0.1 seconds

            } catch (\Exception $e) {
                $errors[] = "Failed to generate question " . ($i + 1) . ": " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => count($generatedQuestions) > 0,
            'questions' => $generatedQuestions,
            'errors' => $errors,
            'generated_count' => count($generatedQuestions),
            'requested_count' => $validated['count']
        ]);
    }

    /**
     * Show AI generation metrics and analytics
     */
    public function metrics()
    {
        // Get all AI-generated questions across all banks
        $mcqQuestions = McqBankQuestion::whereNotNull('generation_metadata')->get();
        $fillBlankQuestions = FillBlankBankQuestion::whereNotNull('generation_metadata')->get();
        $codeQuestions = CodeBankQuestion::whereNotNull('generation_metadata')->get();

        $allQuestions = collect([...$mcqQuestions, ...$fillBlankQuestions, ...$codeQuestions]);

        // Calculate basic stats
        $totalAiQuestions = $allQuestions->count();
        $needsReviewCount = $allQuestions->where('needs_review', true)->count();

        // Calculate quality scores and distribution
        $qualityScores = $allQuestions->pluck('quality_score')->filter()->values();
        $avgQualityScore = $qualityScores->avg() ?? 0;

        $qualityDistribution = [
            'excellent' => $qualityScores->filter(fn($score) => $score >= 90)->count(),
            'good' => $qualityScores->filter(fn($score) => $score >= 80 && $score < 90)->count(),
            'average' => $qualityScores->filter(fn($score) => $score >= 60 && $score < 80)->count(),
            'poor' => $qualityScores->filter(fn($score) => $score < 60)->count(),
        ];

        // Question type distribution
        $questionTypes = [
            'mcq' => $mcqQuestions->count(),
            'fill_blank' => $fillBlankQuestions->count(),
            'code' => $codeQuestions->count(),
        ];

        // Generation trends (last 7 days)
        $generationTrends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $dayQuestions = $allQuestions->filter(function($question) use ($date) {
                return $question->created_at->toDateString() === $date;
            });

            if ($dayQuestions->count() > 0) {
                $generationTrends[] = [
                    'date' => $date,
                    'count' => $dayQuestions->count(),
                    'avg_quality' => $dayQuestions->pluck('quality_score')->filter()->avg() ?? 0,
                ];
            }
        }

        // Recent generations (last 10)
        $recentGenerations = $allQuestions->sortByDesc('created_at')->take(10)->map(function($question) {
            $type = match(get_class($question)) {
                McqBankQuestion::class => 'mcq',
                FillBlankBankQuestion::class => 'fill_blank',
                CodeBankQuestion::class => 'code',
            };

            return [
                'question_type' => $type,
                'topic' => $question->generation_metadata['topic'] ?? 'N/A',
                'quality_score' => $question->quality_score ?? 0,
                'needs_review' => $question->needs_review ?? false,
                'created_at' => $question->created_at,
            ];
        });

        // Mock API stats (in a real implementation, you'd track these in a separate table)
        $apiStats = [
            'api_calls' => $totalAiQuestions * 1.2, // Rough estimate
            'api_success_rate' => 95,
            'avg_response_time' => 2500,
        ];

        $stats = [
            'total_ai_questions' => $totalAiQuestions,
            'success_rate' => $totalAiQuestions > 0 ? round(($totalAiQuestions - $needsReviewCount) / $totalAiQuestions * 100, 1) : 0,
            'needs_review_count' => $needsReviewCount,
            'avg_quality_score' => round($avgQualityScore, 1),
            'quality_distribution' => $qualityDistribution,
            'question_types' => $questionTypes,
            'generation_trends' => $generationTrends,
            'recent_generations' => $recentGenerations,
            'api_calls' => $apiStats['api_calls'],
            'api_success_rate' => $apiStats['api_success_rate'],
            'avg_response_time' => $apiStats['avg_response_time'],
        ];

        return view('ai-questions.metrics', compact('stats'));
    }
}