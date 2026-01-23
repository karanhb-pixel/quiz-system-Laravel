<?php

namespace App\Http\Controllers;

use App\Models\QuestionBank;
use App\Models\QuizTemplate;
use Illuminate\Http\Request;

class QuizTemplateController extends Controller
{
    public function index()
    {
        $templates = QuizTemplate::with('questionBank')
            ->where(function ($query) {
                $query->where('user_id', auth()->id())
                      ->orWhere('is_public', true);
            })
            ->paginate(10);

        $banks = QuestionBank::where('user_id', auth()->id())->get();

        return view('quiz-templates.index', compact('templates', 'banks'));
    }

    public function create()
    {
        $banks = QuestionBank::where('user_id', auth()->id())->get();
        return view('quiz-templates.create', compact('banks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'question_bank_id' => 'required|exists:question_banks,id',
            'total_questions' => 'required|integer|min:1|max:50',
            'mcq_count' => 'required|integer|min:0',
            'fill_blank_count' => 'required|integer|min:0',
            'code_count' => 'required|integer|min:0',
            'difficulty_distribution' => 'required|array',
            'difficulty_distribution.easy' => 'integer|min:0',
            'difficulty_distribution.medium' => 'integer|min:0',
            'difficulty_distribution.hard' => 'integer|min:0',
            'time_limit' => 'nullable|integer|min:1|max:300',
            'is_public' => 'boolean'
        ]);

        $validated['user_id'] = auth()->id();

        // Ensure user owns the question bank
        $bank = QuestionBank::findOrFail($validated['question_bank_id']);
        if ($bank->user_id !== auth()->id()) {
            abort(403);
        }

        // Validate that counts add up to total_questions
        $totalCount = $validated['mcq_count'] + $validated['fill_blank_count'] + $validated['code_count'];
        if ($totalCount !== $validated['total_questions']) {
            return back()->withErrors([
                'mcq_count' => 'Question counts must add up to total questions',
                'fill_blank_count' => 'Question counts must add up to total questions',
                'code_count' => 'Question counts must add up to total questions'
            ]);
        }

        // Validate that there are enough questions in the bank for each type
        $availableMcq = $bank->mcqQuestions()->count();
        $availableFillBlank = $bank->fillBlankQuestions()->count();
        $availableCode = $bank->codeQuestions()->count();

        $errors = [];
        if ($validated['mcq_count'] > $availableMcq) {
            $errors['mcq_count'] = "Only $availableMcq MCQ questions available in this bank";
        }
        if ($validated['fill_blank_count'] > $availableFillBlank) {
            $errors['fill_blank_count'] = "Only $availableFillBlank fill blank questions available in this bank";
        }
        if ($validated['code_count'] > $availableCode) {
            $errors['code_count'] = "Only $availableCode code questions available in this bank";
        }

        if (!empty($errors)) {
            return back()->withErrors($errors);
        }

        // Build config array
        $validated['config'] = [
            'total_questions' => $validated['total_questions'],
            'question_distribution' => [
                'mcq' => $validated['mcq_count'],
                'fill_blank' => $validated['fill_blank_count'],
                'code' => $validated['code_count']
            ],
            'difficulty_distribution' => $validated['difficulty_distribution'],
            'time_limit' => $validated['time_limit'],
            'shuffle_questions' => true,
            'show_results_immediately' => false,
            'max_attempts' => 1
        ];

        // Remove individual fields that are now in config
        unset($validated['total_questions'], $validated['mcq_count'], $validated['fill_blank_count'], $validated['code_count'], $validated['difficulty_distribution'], $validated['time_limit']);

        QuizTemplate::create($validated);

        return redirect()->route('quiz-templates.index')
            ->with('success', 'Quiz template created successfully!');
    }

    public function show(QuizTemplate $template)
    {
        $this->authorize('view', $template);

        $template->load('questionBank');
        $stats = app(\App\Services\QuizGeneratorService::class)->generateQuizStats($template->questionBank);

        return view('quiz-templates.show', compact('template', 'stats'));
    }

    public function edit(QuizTemplate $template)
    {
        $this->authorize('update', $template);

        $banks = QuestionBank::where('user_id', auth()->id())->get();
        return view('quiz-templates.edit', compact('template', 'banks'));
    }

    public function update(Request $request, QuizTemplate $template)
    {
        $this->authorize('update', $template);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'question_bank_id' => 'required|exists:question_banks,id',
            'config' => 'required|array',
            'is_public' => 'boolean'
        ]);

        // Ensure user owns the question bank
        $bank = QuestionBank::findOrFail($validated['question_bank_id']);
        if ($bank->user_id !== auth()->id()) {
            abort(403);
        }

        $template->update($validated);

        return redirect()->route('quiz-templates.show', $template)
            ->with('success', 'Quiz template updated successfully!');
    }

    public function destroy(QuizTemplate $template)
    {
        $this->authorize('delete', $template);

        $template->delete();

        return redirect()->route('quiz-templates.index')
            ->with('success', 'Quiz template deleted successfully!');
    }

    public function take(QuizTemplate $template)
    {
        $this->authorize('view', $template);

        // Generate quiz using template config
        $generator = app(\App\Services\QuizGeneratorService::class);
        $questions = $generator->generateQuiz($template->questionBank, $template->config);

        // Create attempt
        $attempt = \App\Models\BankQuizAttempt::create([
            'user_id' => auth()->id(),
            'question_bank_id' => $template->questionBank->id,
            'total_questions' => count($questions),
            'total_points' => collect($questions)->sum('points'),
            'points_earned' => 0,
            'score_percentage' => 0
        ]);

        // Store template reference
        \App\Models\QuizFromTemplate::create([
            'quiz_template_id' => $template->id,
            'bank_quiz_attempt_id' => $attempt->id,
            'selected_questions' => $questions->pluck('id')->toArray()
        ]);

        session(['bank_quiz_' . $attempt->id => $questions]);

        return view('bank-quiz.attempt', compact('template', 'attempt', 'questions'));
    }
}