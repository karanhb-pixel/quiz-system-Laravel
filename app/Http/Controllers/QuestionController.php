<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function store(Request $request)
{
    $data = $request->validate([
        'quiz_id'        => 'required|exists:quizzes,id',
        'question_text'  => 'required|string',
        'a'              => 'required|string',
        'b'              => 'required|string',
        'c'              => 'required|string',
        'd'              => 'required|string',
        'correct_answer' => 'required|in:a,b,c,d',
    ]);

    Question::create($data);

    return redirect()->back()->with('success', 'Question added successfully!');
}
}
