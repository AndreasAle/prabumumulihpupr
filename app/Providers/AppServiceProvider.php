<?php

namespace App\Providers;

use App\Contracts\AIProviderInterface;
use App\Models\Setting;
use App\Services\PortalKnowledgeProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AIProviderInterface::class, PortalKnowledgeProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.public', 'public.*'], function ($view) {
            $view->with('portalSettings', Setting::pluck('value', 'key'));
        });
    }
}
