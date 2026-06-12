<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('partials.header', function ($view) {
            $activities = Activity::latest()->limit(8)->get();
            $recentCount = Activity::where('created_at', '>=', now()->subHours(24))->count();
            $view->with('headerActivities', $activities)
                 ->with('headerActivityCount', $recentCount);
        });
    }
}
