<?php

namespace App\Policies;

use App\Models\QuestionBank;
use App\Models\User;

class QuestionBankPolicy
{
    public function viewAny(User $user)
    {
        return true; // Anyone can view banks
    }

    public function view(User $user, QuestionBank $bank)
    {
        return true; // Anyone can view banks
    }

    public function create(User $user)
    {
        return true; // Anyone can create banks
    }

    public function update(User $user, QuestionBank $bank)
    {
        return $user->id === $bank->user_id || $user->role === 'admin';
    }

    public function delete(User $user, QuestionBank $bank)
    {
        return $user->id === $bank->user_id || $user->role === 'admin';
    }
}