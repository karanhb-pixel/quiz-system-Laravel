<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

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
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Define Rate Limiter for AI Generation
        // Limits users to 5 quiz generations per hour to protect Gemini API quota
        RateLimiter::for('ai_generations', function (Request $request) {
            return Limit::perHour(5)->by($request->user()?->id ?: $request->ip());
        });

        // Register controller bindings
        $this->app->bind(
            \App\Http\Controllers\QuestionBankController::class,
            function ($app) {
                return new \App\Http\Controllers\QuestionBankController(
                    $app->make(\App\QuestionBank\QuestionBankService::class)
                );
            }
        );
    }
}
