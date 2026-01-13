<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Result;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    
    public function dashboard(){

         $categories = Category::withCount('quizzes')
                        ->orderBy('quizzes_count','desc')
                        ->take(5)
                        ->get();
         $recentResults = Result::where('user_id', auth()->id())
                        ->with('quiz')
                        ->latest()
                        ->limit(5)
                        ->get();

        return view('dashboard',compact('categories','recentResults'));
    }

    public function userAttemptedQuiz(){
        $categories = Category::withCount('quizzes')
                        ->get();
         $recentResults = Result::where('user_id', auth()->id())
                        ->with('quiz')
                        ->latest()
                        ->get();
        return view('userQuizAttempts',compact('categories','recentResults'));
    }

    // 
    public function approve(User $user)
    {
        $user->update(['role' => 'admin']);
        return back()->with('success', "{$user->name} is now an Admin.");
    }

    public function reject(User $user)
    {
        // Reverting them to a standard user
        $user->update(['role' => 'user']);
        return back()->with('success', "Request for {$user->name} was rejected.");
    }

    public function requests()
    {
        // Fetch users waiting for admin approval
        $pendingUsers = User::where('role', 'pending_admin')->latest()->get();

        return view('admin.requests', compact('pendingUsers'));
    }
}
