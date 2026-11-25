<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\CustomerPayment;
use App\Models\CustomerOrder;
use App\Models\Expense;
use App\Models\Inspection;
use App\Models\Movement;
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
        // Both systems: legacy sample_movements and the grouped movements in use today (audit F-16).
        $overdue = fn ($q) => $q->whereIn('status', ['Issued', 'Overdue'])
            ->whereNotNull('expected_return_date')
            ->whereNull('actual_return_date')
            ->where('expected_return_date', '<', now()->toDateString())
            ->count();
        $overdueMovements = $overdue(SampleMovement::query()) + $overdue(Movement::query());

        // ── Finance (only computed for users allowed to see it) ───────────────
        $user = auth()->user();
        $totalInvoiced = $totalReceivable = $overdueInvoices = $invoicesThisMonth = null;
        $paymentsThisMonth = $expensesThisMonth = null;

        if ($user->can('customer-invoices.index')) {
            // Per currency: invoices are in the customer's currency, never summed across (F-16).
            $totalInvoiced = CustomerInvoice::totalsByCurrency('total_amount', ['Cancelled']);
            $totalReceivable = CustomerInvoice::totalsByCurrency('amount_due', ['Paid', 'Cancelled']);
            $overdueInvoices = CustomerInvoice::overdue()->count();
            $invoicesThisMonth = CustomerInvoice::whereMonth('invoice_date', now()->month)
                ->whereYear('invoice_date', now()->year)
                ->whereNotIn('status', ['Cancelled'])
                ->count();
        }

        if ($user->can('customer-payments.index')) {
            $paymentsThisMonth = CustomerPayment::notVoided()->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('actual_pkr_received');
        }

        if ($user->can('expenses.index')) {
            $expensesThisMonth = Expense::notVoided()->whereMonth('expense_date', now()->month)
                ->whereYear('expense_date', now()->year)
                ->sum('amount');
        }

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
        $recentActivity = $user->can('activities.view')
            ? Activity::with('causer')->latest()->limit(5)->get()
            : null;

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
