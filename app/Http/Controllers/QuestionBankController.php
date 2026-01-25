<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\QuestionBank\QuestionBankService;
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
    
        // Generate and store questions, associating them with the quiz
        $questions = $this->questionBankService->generateAndStoreQuestions(
            $topic,
            $difficulty,
            $numQuestions,
            $questionType,
            $categoryId,
            $quiz->id // Pass the quiz ID to associate questions with the quiz
        );
    
        // For view response
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'questions' => $questions,
                'quiz' => $quiz,
                'message' => "Generated $numQuestions $questionType questions about $topic with $difficulty difficulty"
            ]);
        }
    
        // For regular form submission
        return redirect()->back()->with([
            'success' => "Generated $numQuestions $questionType questions about $topic with $difficulty difficulty",
            'generatedQuestions' => $questions,
            'quiz' => $quiz
        ]);
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