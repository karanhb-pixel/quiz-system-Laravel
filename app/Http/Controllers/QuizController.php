<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Quiz;
use App\Models\Result;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $categories = Category::get();
        $quizzes = Quiz::where('user_id', auth()->id())
                        ->with('category')
                        ->latest()
                        ->get();

        return view('quizzes.index',[
            'categories'=>$categories,
            'quizzes' => $quizzes
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('quizzes.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'title'=>[
                'required',
                'string',
                'max:255'
            ],
            'category_id'=>[
                'required',
                'exists:categories,id'
            ]
            ]);


            $quiz = Quiz::create([
                'title' => $request->title,
                'category_id' => $request->category_id,
                'user_id' => auth()->id(),
            ]);

            // return "Quiz created with ID: " . $quiz->id;
            return redirect()->route('quizzes.index')
                            ->with('success','Quiz Created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Quiz $quiz)
    {

        $quiz->load('questions');

        return view('quizzes.show', compact('quiz'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quiz $quiz)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Quiz $quiz)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quiz $quiz)
    {
         if($quiz->delete()){
            return redirect()->route('quizzes.index')
                        ->with('success','Quiz Deleted Successfully!');
        }   

        return redirect()->back()
                        ->with('error','Something Wrong, Quiz could not be deleted.');
    }


    public function showByCategory(Category $category){
            // we Get all quiz in category regardless who created it.
             $quizzes = $category->quizzes()
                    ->with('user')
                    ->withCount('questions')
                    ->latest()
                    ->get();
            
            if(!$quizzes || $quizzes->isEmpty()){
                return redirect()->back()
                                ->with('error','No quizzes found in this category.');
            }
            
            return view('quizzes.public_index', compact('category', 'quizzes'));
    }

    public function attempt(Quiz $quiz){

         $quiz->load('questions');
        return view('quizzes.attempt',compact('quiz'));
    }

    public function submit(Request $request, Quiz $quiz)
    {
        $userAnswers = $request->input('answers', []);
        
        // Create a pending result
        $result = Result::create([
            'user_id' => auth()->id(),
            'quiz_id' => $quiz->id,
            'total_questions' => $quiz->questions->count(),
            'correct_answers' => 0,
            'score_percentage' => 0,
            'user_answers' => json_encode($userAnswers),
            'status' => 'pending'
        ]);

        return redirect()->route('quizzes.result', $result->id);
    }

    public function showResult(Result $result)
    {
        $result->load('quiz.questions');
        $data = json_decode($result->user_answers, true) ?: [];
        
        // Handle both pending (flat array) and completed (nested array) structures
        $userAnswers = isset($data['answers']) ? $data['answers'] : $data;
        $aiEvaluations = isset($data['evaluations']) ? $data['evaluations'] : [];
        
        return view('quizzes.result', [
            'result'      => $result,
            'quiz'        => $result->quiz,
            'userAnswers' => $userAnswers,
            'aiEvaluations' => $aiEvaluations
        ]);
    }

    public function evaluate(Result $result, \App\Services\GeminiService $gemini)
    {
        if ($result->status === 'completed') {
            return response()->json([
                'status' => 'completed',
                'percentage' => $result->score_percentage,
                'score' => $result->correct_answers
            ]);
        }

        $userAnswers = json_decode($result->user_answers, true) ?: [];
        $questions = $result->quiz->questions;

        $correctCount = 0;
        $aiEvaluations = [];

        $stripExtras = function($code) {
           $code = preg_replace('/<\?php|\?>/', '', $code);
           $code = preg_replace('!/\*.*?\*/!s', '', $code);
           $code = preg_replace('!//.*?\n!', "\n", $code);
           return preg_replace('/\s+/', '', strtolower($code));
        };

        foreach ($questions as $question) {
            $submittedAnswer = $userAnswers[$question->id] ?? null;
            $isCorrect = false;

            if ($question->question_type === 'mcq') {
                $isCorrect = ($submittedAnswer === $question->correct_answer);
            } elseif ($question->question_type === 'fill_blank') {
                $isCorrect = (trim(strtolower($submittedAnswer ?? '')) === trim(strtolower($question->correct_answer)));
            } elseif ($question->question_type === 'code') {
                if (empty(trim($submittedAnswer ?? ''))) {
                    $isCorrect = false;
                    $aiEvaluations[$question->id] = ['is_correct' => false, 'explanation' => 'No answer provided.'];
                } else {
                    $submittedNormalized = $stripExtras($submittedAnswer);
                    $correctNormalized = $stripExtras($question->correct_answer);
                    
                    if ($submittedNormalized === $correctNormalized) {
                        $isCorrect = true;
                        $aiEvaluations[$question->id] = ['is_correct' => true, 'explanation' => 'Exact logic match.'];
                    } else {
                        $evaluation = $gemini->evaluateCode($question->question_text, $submittedAnswer, $question->correct_answer, $question->hint);
                        $isCorrect = $evaluation['is_correct'] ?? false;
                        $aiEvaluations[$question->id] = $evaluation;
                    }
                }
            }

            if ($isCorrect) {
                $correctCount++;
            }
        }

        $totalQuestions = $questions->count();
        $percentage = ($totalQuestions > 0) ? ($correctCount / $totalQuestions) * 100 : 0;

        $result->update([
            'correct_answers' => $correctCount,
            'score_percentage' => $percentage,
            'status' => 'completed',
            'user_answers' => json_encode([
                'answers' => $userAnswers,
                'evaluations' => $aiEvaluations
            ])
        ]);

        return response()->json([
            'status' => 'completed',
            'percentage' => round($percentage, 2),
            'score' => $correctCount,
            'total' => $totalQuestions,
            'aiEvaluations' => $aiEvaluations
        ]);
    }
}
