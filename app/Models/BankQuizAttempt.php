<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankQuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'question_bank_id',
        'total_questions',
        'total_points',
        'points_earned',
        'score_percentage',
        'completed_at'
    ];

    protected $casts = [
        'total_questions' => 'integer',
        'total_points' => 'integer',
        'points_earned' => 'decimal:2',
        'score_percentage' => 'decimal:2',
        'completed_at' => 'datetime'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function questionBank()
    {
        return $this->belongsTo(QuestionBank::class);
    }

    public function responses()
    {
        return $this->hasMany(BankQuestionResponse::class);
    }

    // Accessors
    public function getIsCompletedAttribute()
    {
        return !is_null($this->completed_at);
    }
}