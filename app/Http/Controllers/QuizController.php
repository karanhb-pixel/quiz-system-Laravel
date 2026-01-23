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
                'nullable',
                'exists:categories,id'
            ]
        ]);

        // If "All Categories" is selected (empty value), use the first available category
        if (empty($request->category_id)) {
            $firstCategory = Category::first();
            if ($firstCategory) {
                $request->merge(['category_id' => $firstCategory->id]);
            } else {
                return back()->withErrors(['category_id' => 'No categories available. Please create a category first.']);
            }
        }


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

    public function submit(Request $request,Quiz $quiz){
        // Get answers for form
        $userAnswers = $request->input('answers',[]);
        $questions = $quiz->questions;

        $correctCount = 0;
        $totalQuestions = $questions->count();

        // Loop throuth questions for check correct answer
        foreach($questions as $question){
            $submittedAnswer = $userAnswers[$question->id] ?? null;

            if($submittedAnswer === $question->correct_answer){
                $correctCount++;
            }

        }

        // calculate percentage
        $percentage = ($totalQuestions > 0) ? ($correctCount/$totalQuestions) * 100 : 0;

        // save to Database
        Result::create([
            'user_id' => auth()->id(),
            'quiz_id' => $quiz->id,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctCount,
            'score_percentage' => $percentage 
        ]);

        return view('quizzes.result',[
            'quiz'       => $quiz,
            'score'      => $correctCount,
            'total'      => $totalQuestions,
            'percentage' => $percentage,
            'userAnswers' => $userAnswers
        ]);

        
    }


}
