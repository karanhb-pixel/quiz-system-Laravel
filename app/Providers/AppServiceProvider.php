<?php

namespace App\Providers;

use App\Models\QuestionBank;
use App\Models\QuizTemplate;
use App\Policies\QuestionBankPolicy;
use App\Policies\QuizTemplatePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(QuestionBank::class, QuestionBankPolicy::class);
        Gate::policy(QuizTemplate::class, QuizTemplatePolicy::class);
    }
}
