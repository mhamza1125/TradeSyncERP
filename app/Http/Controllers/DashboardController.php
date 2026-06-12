<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\CustomerPayment;
use App\Models\CustomerOrder;
use App\Models\Expense;
use App\Models\Inspection;
use App\Models\Sample;
use App\Models\SampleMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function __invoke()
    {
        // ── Customers ──────────────────────────────────────────────────────────
        $totalCustomers = Customer::where('status', true)->count();

        // ── Samples ────────────────────────────────────────────────────────────
        $activeSamples  = Sample::whereNotIn('status', ['Completed', 'Returned'])->count();
        $overdueSamples = Sample::whereNotIn('status', ['Completed', 'Returned'])
            ->whereRaw('DATE_ADD(receive_date, INTERVAL alert_days DAY) < CURDATE()')
            ->count();

        // ── Customer Orders ────────────────────────────────────────────────────
        $pendingOrders = CustomerOrder::whereIn('status', ['Draft', 'Confirmed', 'Processing'])->count();

        // ── Inspections ────────────────────────────────────────────────────────
        $pendingInspections = Inspection::where('overall_status', 'Pending')->count();

        // ── Movements ─────────────────────────────────────────────────────────
        $overdueMovements = SampleMovement::where('status', 'Issued')
            ->whereNotNull('expected_return_date')
            ->whereNull('actual_return_date')
            ->where('expected_return_date', '<', now()->toDateString())
            ->count();

        // ── Invoices ───────────────────────────────────────────────────────────
        $totalInvoiced      = CustomerInvoice::whereNotIn('status', ['Cancelled'])->sum('total_amount');
        $totalReceivable    = CustomerInvoice::whereNotIn('status', ['Paid', 'Cancelled'])->sum('amount_due');
        $overdueInvoices    = CustomerInvoice::where('status', 'Overdue')->count();
        $invoicesThisMonth  = CustomerInvoice::whereMonth('invoice_date', now()->month)
            ->whereYear('invoice_date', now()->year)
            ->whereNotIn('status', ['Cancelled'])
            ->count();

        // ── Payments received this month ───────────────────────────────────────
        $paymentsThisMonth  = CustomerPayment::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('actual_pkr_received');

        // ── Expenses this month ────────────────────────────────────────────────
        $expensesThisMonth = Expense::whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        // ── Samples by status (for mini chart) ────────────────────────────────
        $samplesByStatus = Sample::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // ── Recent customer orders ─────────────────────────────────────────────
        $recentOrders = CustomerOrder::with('customer')
            ->latest('order_date')
            ->limit(5)
            ->get();

        // ── Recent activity ────────────────────────────────────────────────────
        $recentActivity = Activity::with('causer')
            ->latest()
            ->limit(5)
            ->get();

        // ── Currently active users (session active within last 30 minutes) ────
        $activeCutoff = now()->subMinutes(30)->timestamp;
        $activeUserIds = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', $activeCutoff)
            ->distinct()
            ->pluck('user_id');

        $activeSessions = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', $activeCutoff)
            ->get()
            ->groupBy('user_id');

        $loggedInUsers = User::whereIn('id', $activeUserIds)->get()
            ->map(function ($user) use ($activeSessions) {
                $sessions = $activeSessions->get($user->id, collect());
                $user->last_activity_ts = $sessions->max('last_activity');
                $user->session_count    = $sessions->count();
                return $user;
            })
            ->sortByDesc('last_activity_ts');

        return view('dashboard', compact(
            'totalCustomers',
            'activeSamples',
            'overdueSamples',
            'pendingOrders',
            'pendingInspections',
            'overdueMovements',
            'totalInvoiced',
            'totalReceivable',
            'overdueInvoices',
            'invoicesThisMonth',
            'paymentsThisMonth',
            'expensesThisMonth',
            'samplesByStatus',
            'recentOrders',
            'recentActivity',
            'loggedInUsers'
        ));
    }
}
