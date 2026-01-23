<?php

namespace App\Http\Controllers;

use App\Models\CodeBankQuestion;
use App\Models\FillBlankBankQuestion;
use App\Models\McqBankQuestion;
use App\Models\QuestionBank;
use Illuminate\Http\Request;

class BankQuestionController extends Controller
{
    public function create(QuestionBank $bank, $type = 'mcq')
    {
        $this->authorize('update', $bank);

        // Validate question type
        if (!in_array($type, ['mcq', 'fill_blank', 'code'])) {
            abort(404);
        }

        return view('bank-questions.create', compact('bank', 'type'));
    }

    public function store(Request $request, QuestionBank $bank)
    {
        $this->authorize('update', $bank);

        $type = $request->input('question_type', 'mcq');

        switch ($type) {
            case 'mcq':
                $validated = $request->validate([
                    'question_text' => 'required|string',
                    'option_a' => 'required|string|max:255',
                    'option_b' => 'required|string|max:255',
                    'option_c' => 'required|string|max:255',
                    'option_d' => 'required|string|max:255',
                    'correct_answer' => 'required|in:a,b,c,d',
                    'points' => 'required|integer|min:1|max:10'
                ]);

                $validated['question_bank_id'] = $bank->id;
                McqBankQuestion::create($validated);
                break;

            case 'fill_blank':
                $validated = $request->validate([
                    'question_text' => 'required|string',
                    'expected_answer' => 'required|string',
                    'evaluation_hints' => 'nullable|string',
                    'case_sensitive' => 'boolean',
                    'points' => 'required|integer|min:1|max:10'
                ]);

                $validated['question_bank_id'] = $bank->id;
                $validated['case_sensitive'] = $request->boolean('case_sensitive');
                FillBlankBankQuestion::create($validated);
                break;

            case 'code':
                $validated = $request->validate([
                    'question_text' => 'required|string',
                    'expected_code' => 'required|string',
                    'evaluation_criteria' => 'nullable|string',
                    'language' => 'required|in:' . implode(',', array_keys(CodeBankQuestion::getAvailableLanguages())),
                    'points' => 'required|integer|min:1|max:10'
                ]);

                $validated['question_bank_id'] = $bank->id;
                CodeBankQuestion::create($validated);
                break;

            default:
                abort(400, 'Invalid question type');
        }

        return redirect()->route('question-banks.show', $bank)
            ->with('success', ucfirst(str_replace('_', ' ', $type)) . ' question added successfully!');
    }

    public function edit(QuestionBank $bank, $question, $type)
    {
        $this->authorize('update', $bank);

        // Find the question based on type
        switch ($type) {
            case 'mcq':
                $question = McqBankQuestion::findOrFail($question);
                break;
            case 'fill_blank':
                $question = FillBlankBankQuestion::findOrFail($question);
                break;
            case 'code':
                $question = CodeBankQuestion::findOrFail($question);
                break;
            default:
                abort(404);
        }

        return view('bank-questions.edit', compact('bank', 'question', 'type'));
    }

    public function update(Request $request, QuestionBank $bank, $question, $type)
    {
        $this->authorize('update', $bank);

        // Find and update the question based on type
        switch ($type) {
            case 'mcq':
                $question = McqBankQuestion::findOrFail($question);
                $validated = $request->validate([
                    'question_text' => 'required|string',
                    'option_a' => 'required|string|max:255',
                    'option_b' => 'required|string|max:255',
                    'option_c' => 'required|string|max:255',
                    'option_d' => 'required|string|max:255',
                    'correct_answer' => 'required|in:a,b,c,d',
                    'points' => 'required|integer|min:1|max:10'
                ]);
                break;

            case 'fill_blank':
                $question = FillBlankBankQuestion::findOrFail($question);
                $validated = $request->validate([
                    'question_text' => 'required|string',
                    'expected_answer' => 'required|string',
                    'evaluation_hints' => 'nullable|string',
                    'case_sensitive' => 'boolean',
                    'points' => 'required|integer|min:1|max:10'
                ]);
                $validated['case_sensitive'] = $request->boolean('case_sensitive');
                break;

            case 'code':
                $question = CodeBankQuestion::findOrFail($question);
                $validated = $request->validate([
                    'question_text' => 'required|string',
                    'expected_code' => 'required|string',
                    'evaluation_criteria' => 'nullable|string',
                    'language' => 'required|in:' . implode(',', array_keys(CodeBankQuestion::getAvailableLanguages())),
                    'points' => 'required|integer|min:1|max:10'
                ]);
                break;

            default:
                abort(400, 'Invalid question type');
        }

        $question->update($validated);

        return redirect()->route('question-banks.show', $bank)
            ->with('success', ucfirst(str_replace('_', ' ', $type)) . ' question updated successfully!');
    }

    public function destroy(QuestionBank $bank, $question, $type)
    {
        $this->authorize('update', $bank);

        // Find and delete the question based on type
        switch ($type) {
            case 'mcq':
                $question = McqBankQuestion::findOrFail($question);
                break;
            case 'fill_blank':
                $question = FillBlankBankQuestion::findOrFail($question);
                break;
            case 'code':
                $question = CodeBankQuestion::findOrFail($question);
                break;
            default:
                abort(404);
        }

        $question->delete();

        return redirect()->route('question-banks.show', $bank)
            ->with('success', ucfirst(str_replace('_', ' ', $type)) . ' question deleted successfully!');
    }
}