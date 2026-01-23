<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CodeBankQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_bank_id',
        'question_text',
        'expected_code',
        'evaluation_criteria',
        'language',
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

    // Get question type for polymorphic relationships
    public function getQuestionTypeAttribute()
    {
        return 'code';
    }

    // Get available languages
    public static function getAvailableLanguages()
    {
        return [
            'css' => 'CSS',
            'html' => 'HTML',
            'javascript' => 'JavaScript',
            'python' => 'Python',
            'php' => 'PHP',
            'java' => 'Java',
            'cpp' => 'C++',
            'csharp' => 'C#'
        ];
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