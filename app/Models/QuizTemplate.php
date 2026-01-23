<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'question_bank_id',
        'user_id',
        'config',
        'is_public'
    ];

    protected $casts = [
        'config' => 'array',
        'is_public' => 'boolean'
    ];

    // Relationships
    public function questionBank()
    {
        return $this->belongsTo(QuestionBank::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quizzes()
    {
        return $this->hasMany(QuizFromTemplate::class);
    }

    // Accessors
    public function getDefaultConfig()
    {
        return [
            'question_distribution' => [
                'mcq' => 25,
                'fill_blank' => 25,
                'code' => 50
            ],
            'total_questions' => 10,
            'time_limit' => null, // minutes
            'shuffle_questions' => true,
            'show_results_immediately' => false,
            'max_attempts' => 1,
            'scoring' => [
                'mcq_weight' => 1.0,
                'fill_blank_weight' => 2.0,
                'code_weight' => 3.0
            ]
        ];
    }

    // Policies
    public function getIsOwnedByAttribute()
    {
        return $this->user_id === auth()->id();
    }

    public function getCanEditAttribute()
    {
        return $this->is_owned_by || auth()->user()->role === 'admin';
    }
}