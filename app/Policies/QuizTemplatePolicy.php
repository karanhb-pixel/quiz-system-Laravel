<?php

namespace App\Policies;

use App\Models\QuizTemplate;
use App\Models\User;

class QuizTemplatePolicy
{
    public function view(User $user, QuizTemplate $template)
    {
        return $template->user_id === $user->id || $template->is_public;
    }

    public function create(User $user)
    {
        return true; // Any authenticated user can create templates
    }

    public function update(User $user, QuizTemplate $template)
    {
        return $template->user_id === $user->id || $user->role === 'admin';
    }

    public function delete(User $user, QuizTemplate $template)
    {
        return $template->user_id === $user->id || $user->role === 'admin';
    }
}