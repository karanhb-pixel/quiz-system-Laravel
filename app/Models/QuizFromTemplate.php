<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizFromTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_template_id',
        'bank_quiz_attempt_id',
        'selected_questions'
    ];

    protected $casts = [
        'selected_questions' => 'array'
    ];

    // Relationships
    public function template()
    {
        return $this->belongsTo(QuizTemplate::class, 'quiz_template_id');
    }

    public function attempt()
    {
        return $this->belongsTo(BankQuizAttempt::class, 'bank_quiz_attempt_id');
    }

    // Accessors
    public function getQuestionCountAttribute()
    {
        return count($this->selected_questions ?? []);
    }

    public function getQuestionTypeBreakdownAttribute()
    {
        if (!$this->selected_questions) {
            return [];
        }

        $breakdown = ['mcq' => 0, 'fill_blank' => 0, 'code' => 0];

        foreach ($this->selected_questions as $questionId) {
            // This would need to be enhanced to actually check question types
            // For now, return placeholder
            $breakdown['mcq']++;
        }

        return $breakdown;
    }
}