<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankQuestionResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_quiz_attempt_id',
        'question_type',
        'question_id',
        'user_answer',
        'is_correct',
        'points_earned',
        'ai_feedback',
        'ai_confidence'
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'points_earned' => 'decimal:2',
        'ai_confidence' => 'decimal:2'
    ];

    // Relationship to attempt
    public function attempt()
    {
        return $this->belongsTo(BankQuizAttempt::class, 'bank_quiz_attempt_id');
    }

    // Polymorphic relationship to question
    public function question()
    {
        return $this->morphTo(null, 'question_type', 'question_id');
    }

    // Get question type label
    public function getQuestionTypeLabelAttribute()
    {
        return match($this->question_type) {
            'mcq' => 'Multiple Choice',
            'fill_blank' => 'Fill in the Blank',
            'code' => 'Code',
            default => 'Unknown'
        };
    }
}