<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class McqBankQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_bank_id',
        'question_text',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_answer',
        'points',
        'generated_by_ai',
        'needs_review',
        'generation_metadata',
        'difficulty'
    ];

    protected $casts = [
        'points' => 'integer'
    ];

    // Relationship to QuestionBank
    public function questionBank()
    {
        return $this->belongsTo(QuestionBank::class);
    }

    // Get correct option text
    public function getCorrectOptionTextAttribute()
    {
        return $this->{'option_' . strtolower($this->correct_answer)};
    }

    // Invalidate cache when questions are created, updated, or deleted
    protected static function boot()
    {
        parent::boot();

        static::created(function ($question) {
            Cache::forget("bank:{$question->question_bank_id}:questions");
        });

        static::updated(function ($question) {
            Cache::forget("bank:{$question->question_bank_id}:questions");
        });

        static::deleted(function ($question) {
            Cache::forget("bank:{$question->question_bank_id}:questions");
        });
    }
}