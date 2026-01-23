<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\QuestionBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionBankController extends Controller
{
    public function index()
    {
        $banks = QuestionBank::with(['category', 'user'])
            ->withCount('mcqQuestions')
            ->paginate(10);

        return view('question-banks.index', compact('banks'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('question-banks.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'questions_per_quiz' => 'required|integer|min:1|max:50'
        ]);

        $validated['user_id'] = Auth::id();

        QuestionBank::create($validated);

        return redirect()->route('question-banks.index')
            ->with('success', 'Question bank created successfully!');
    }

    public function show(QuestionBank $bank)
    {
        $bank->load(['category', 'user', 'mcqQuestions']);
        return view('question-banks.show', compact('bank'));
    }

    public function edit(QuestionBank $bank)
    {
        $this->authorize('update', $bank);

        $categories = Category::all();
        return view('question-banks.edit', compact('bank', 'categories'));
    }

    public function update(Request $request, QuestionBank $bank)
    {
        $this->authorize('update', $bank);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'questions_per_quiz' => 'required|integer|min:1|max:50'
        ]);

        $bank->update($validated);

        return redirect()->route('question-banks.show', $bank)
            ->with('success', 'Question bank updated successfully!');
    }

    public function destroy(QuestionBank $bank)
    {
        $this->authorize('delete', $bank);

        $bank->delete();

        return redirect()->route('question-banks.index')
            ->with('success', 'Question bank deleted successfully!');
    }
}