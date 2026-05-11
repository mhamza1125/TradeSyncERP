<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\Sample;
use App\Models\SampleMovement;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $activeSamples   = Sample::whereNotIn('status', ['Completed', 'Returned'])->count();
        $overdueSamples  = Sample::whereNotIn('status', ['Completed', 'Returned'])
            ->whereRaw('DATE_ADD(receive_date, INTERVAL alert_days DAY) < CURDATE()')
            ->count();
        $overdueMovements = SampleMovement::where('status', 'Issued')
            ->whereNotNull('expected_return_date')
            ->whereNull('actual_return_date')
            ->where('expected_return_date', '<', now()->toDateString())
            ->count();
        $pendingInspections = Inspection::where('overall_status', 'Pending')->count();

        $recentActivity = Activity::with('causer')
            ->latest()
            ->limit(15)
            ->get();

        return view('dashboard', compact(
            'activeSamples',
            'overdueSamples',
            'overdueMovements',
            'pendingInspections',
            'recentActivity'
        ));
    }
}
