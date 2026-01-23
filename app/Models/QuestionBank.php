<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QuestionBank extends Model
{
    use HasFactory;
       protected $fillable = [
        'name',
        'slug',
        'description',
        'category_id',
        'user_id',
        'questions_per_quiz'
    ];

     protected $casts = [
        'questions_per_quiz' => 'integer'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mcqQuestions()
    {
        return $this->hasMany(McqBankQuestion::class);
    }

    public function fillBlankQuestions()
    {
        return $this->hasMany(FillBlankBankQuestion::class);
    }

    public function codeQuestions()
    {
        return $this->hasMany(CodeBankQuestion::class);
    }

    public function attempts()
    {
        return $this->hasMany(BankQuizAttempt::class);
    }

    // Get total question count
    public function getTotalQuestionsAttribute()
    {
        return $this->mcqQuestions()->count() +
               $this->fillBlankQuestions()->count() +
               $this->codeQuestions()->count();
    }

    // Auto-generate slug from name and invalidate cache
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bank) {
            $bank->slug = Str::slug($bank->name);
        });

        static::updating(function ($bank) {
            if ($bank->isDirty('name')) {
                $bank->slug = Str::slug($bank->name);
            }
        });

        static::deleted(function ($bank) {
            \Illuminate\Support\Facades\Cache::forget("bank:{$bank->id}:questions");
        });
    }
}
