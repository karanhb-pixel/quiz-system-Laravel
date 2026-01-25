<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function store(Request $request)
    {
        $rules = [
            'quiz_id'        => 'required|exists:quizzes,id',
            'question_text'  => 'required|string',
            'question_type'  => 'required|in:mcq,fill_blank,code',
            'hint'           => 'nullable|string',
        ];

        if ($request->question_type === 'mcq') {
            $rules['a'] = 'required|string';
            $rules['b'] = 'required|string';
            $rules['c'] = 'required|string';
            $rules['d'] = 'required|string';
            $rules['correct_answer'] = 'required|in:a,b,c,d';
        } else {
            // fill_blank and code both store answer in correct_answer column
            $rules['correct_answer'] = 'required|string';
        }

        $data = $request->validate($rules);

        // Ensure optional fields are present even if null, to avoid DB errors if strict
        if ($request->question_type === 'fill_blank' || $request->question_type === 'code') {
            $data['a'] = $data['b'] = $data['c'] = $data['d'] = '';
        }

        Question::create($data);

        return redirect()->back()->with('success', 'Question added successfully!');
    }
}
