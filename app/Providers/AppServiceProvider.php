<?php

namespace App\Providers;

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
