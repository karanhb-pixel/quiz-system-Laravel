<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FillBlankBankQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_bank_id',
        'question_text',
        'expected_answer',
        'evaluation_hints',
        'case_sensitive',
        'points',
        'generated_by_ai',
        'needs_review',
        'generation_metadata',
        'difficulty'
    ];

    protected $casts = [
        'case_sensitive' => 'boolean',
        'points' => 'integer'
    ];

    // Relationship to QuestionBank
    public function questionBank()
    {
        return $this->belongsTo(QuestionBank::class);
    }

    // Get question type for polymorphic relationships
    public function getQuestionTypeAttribute()
    {
        return 'fill_blank';
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