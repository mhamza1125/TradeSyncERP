<?php

namespace App\Providers;

use App\Services\Pdf\PdfPageNumberService;
use Dompdf\Dompdf;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Every Dompdf instance gets the page-number callback (opt-in per template).
        $this->app->extend('dompdf', fn (Dompdf $dompdf) => PdfPageNumberService::attach($dompdf));
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Block db:wipe, migrate:fresh/refresh/reset/rollback on the live server
        DB::prohibitDestructiveCommands($this->app->isProduction());

        View::composer('partials.header', function ($view) {
            // The activity feed is part of the audit log: only for users who may read it.
            $canSee = (bool) auth()->user()?->can('activities.view');
            $activities = $canSee ? Activity::latest()->limit(8)->get() : collect();
            $recentCount = $canSee ? Activity::where('created_at', '>=', now()->subHours(24))->count() : 0;
            $view->with('headerActivities', $activities)
                 ->with('headerActivityCount', $recentCount);
        });
    }
}
