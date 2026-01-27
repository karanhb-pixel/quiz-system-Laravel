<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\QuestionBank\QuestionBankService;
use App\Jobs\GenerateQuizQuestionsJob;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models;

class QuestionBankController extends Controller
{
    protected $questionBankService;

    public function __construct(QuestionBankService $questionBankService)
    {
        $this->questionBankService = $questionBankService;
    }

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
    
        // Create a quiz with the topic as the quiz name
        $quiz = Quiz::create([
            'title' => $topic,
            'category_id' => $categoryId,
            'description' => $instructions,
            'slug' => Str::slug($topic),
            'user_id' => auth()->id(),
        ]);
    
        // Dispatch to background queue instead of running immediately
        GenerateQuizQuestionsJob::dispatch(
            $quiz->id,
            $topic,
            $difficulty,
            $numQuestions,
            $questionType,
            $categoryId
        );
    
        // For view response
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'quiz' => $quiz,
                'message' => "Quiz created! Questions are being generated in the background."
            ]);
        }
    
        // For regular form submission
        return redirect()->route('quizzes.show', $quiz->slug)
                        ->with('success', "Quiz '{$quiz->title}' created! Questions are being generated in the background. Refresh in a few moments to see them.");
    }

    public function showGenerationForm()
    {
        $categories = \App\Models\Category::all();
        return view('admin.question-bank', compact('categories'));
    }

    public function getQuestionsByTopic($topic)
    {
        $questions = $this->questionBankService->getQuestionsByTopic($topic);

        return response()->json([
            'success' => true,
            'questions' => $questions
        ]);
    }
}