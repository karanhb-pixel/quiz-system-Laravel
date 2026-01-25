<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'category_id',
        'user_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quiz) {
            // Automatically create a slug from the title if it's empty
            if (empty($quiz->slug)) {
                $quiz->slug = Str::slug($quiz->title);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function questions(){
            return $this->hasMany(Question::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
