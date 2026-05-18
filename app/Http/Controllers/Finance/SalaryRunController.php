<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreSalaryRunRequest;
use App\Http\Requests\Finance\UpdateSalaryRunLinesRequest;
use App\Models\Account;
use App\Models\Employee;
use App\Models\SalaryRun;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalaryRunController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:salary.index')->only(['index', 'show']);
        $this->middleware('permission:salary.create')->only(['create', 'store']);
        $this->middleware('permission:salary.edit')->only('updateLines');
        $this->middleware('permission:salary.pay')->only('pay');
    }

    public function index()
    {
        $runs = SalaryRun::with(['account', 'processedBy'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('finance.salary.index', compact('runs'));
    }

    public function create()
    {
        $accounts = Account::where('status', true)->whereIn('account_type', ['Cash', 'Bank'])->get();
        return view('finance.salary.create', compact('accounts'));
    }

    public function store(StoreSalaryRunRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $run = SalaryRun::create([
                'month'        => $request->month,
                'account_id'   => $request->account_id,
                'processed_by' => auth()->id(),
                'status'       => 'Draft',
            ]);

            $employees = Employee::where('status', true)->get();

            foreach ($employees as $employee) {
                $run->lines()->create([
                    'employee_id'  => $employee->id,
                    'basic_salary' => $employee->basic_salary,
                    'bonus'        => 0,
                    'deduction'    => 0,
                    'advance'      => 0,
                ]);
            }

            $run->update(['total_net_payable' => $run->lines()->sum(DB::raw('basic_salary + bonus - deduction - advance'))]);

            return redirect()->route('salary.show', $run)
                ->with('success', "Salary run for {$run->month} generated successfully.");
        });
    }

    public function show(SalaryRun $salaryRun)
    {
        $salaryRun->load(['lines.employee', 'account', 'processedBy', 'transaction']);
        return view('finance.salary.show', compact('salaryRun'));
    }

    public function updateLines(UpdateSalaryRunLinesRequest $request, SalaryRun $salaryRun)
    {
        abort_if($salaryRun->isPaid(), 403, 'Cannot edit a paid salary run.');

        return DB::transaction(function () use ($request, $salaryRun) {
            $total = 0;

            foreach ($request->lines as $lineData) {
                $line = $salaryRun->lines()->findOrFail($lineData['id']);
                $leaveDeduct = $lineData['leave_deduction_amount'] ?? 0;
                $line->update([
                    'basic_salary'           => $lineData['basic_salary'],
                    'bonus'                  => $lineData['bonus'] ?? 0,
                    'deduction'              => $lineData['deduction'] ?? 0,
                    'advance'                => $lineData['advance'] ?? 0,
                    'leave_days'             => $lineData['leave_days'] ?? 0,
                    'leave_deduction_amount' => $leaveDeduct,
                    'remarks'                => $lineData['remarks'] ?? null,
                ]);
                $total += $lineData['basic_salary'] + ($lineData['bonus'] ?? 0)
                    - ($lineData['deduction'] ?? 0) - ($lineData['advance'] ?? 0)
                    - $leaveDeduct;
            }

            $salaryRun->update(['total_net_payable' => $total]);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'total' => $total]);
            }

            return redirect()->route('salary.show', $salaryRun)
                ->with('success', 'Salary lines updated.');
        });
    }

    public function pay(Request $request, SalaryRun $salaryRun)
    {
        abort_if($salaryRun->isPaid(), 403, 'Already paid.');

        return DB::transaction(function () use ($request, $salaryRun) {
            $request->validate([
                'payment_date' => ['required', 'date'],
            ]);

            $transaction = Transaction::create([
                'transaction_date'  => $request->payment_date,
                'transaction_type'  => 'Salary',
                'reference_type'    => 'salary_run',
                'reference_id'      => $salaryRun->id,
                'debit_account_id'  => $salaryRun->account_id,
                'credit_account_id' => $salaryRun->account_id,
                'amount'            => $salaryRun->total_net_payable,
                'remarks'           => "Salary for {$salaryRun->month}",
                'created_by'        => auth()->id(),
            ]);

            $salaryRun->update([
                'status'         => 'Paid',
                'payment_date'   => $request->payment_date,
                'transaction_id' => $transaction->id,
            ]);

            if ($request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()->route('salary.show', $salaryRun)
                ->with('success', 'Salary run marked as paid.');
        });
    }
}
