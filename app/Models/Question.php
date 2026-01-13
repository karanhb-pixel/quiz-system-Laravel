<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Question extends Model
{
  protected $fillable = [
    'quiz_id',
    'question_text',
    'slug',
    'a', 'b', 'c', 'd', // Match the form names exactly
    'correct_answer'
    ];

  protected static function boot()
  {
      parent::boot();

      static::creating(function ($question) {
          // Automatically create a slug from the question_text if it's empty
          if (empty($question->slug)) {
              $question->slug = Str::slug($question->question_text);
          }
      });
  }

    public function quiz(){
        return $this->belongsTo(Quiz::class);
    }

}
