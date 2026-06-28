<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\StoreEmployeeRequest;
use App\Http\Requests\Masters\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\EmployeeExperience;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:employees.index')->only(['index', 'show', 'exportPdf', 'exportSinglePdf']);
        $this->middleware('permission:employees.create')->only(['create', 'store']);
        $this->middleware('permission:employees.edit')->only(['edit', 'update']);
        $this->middleware('permission:employees.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $employees = Employee::query()
            ->when($request->search, fn ($q, $s) => $q->where('employee_name', 'like', "%{$s}%")
                ->orWhere('department', 'like', "%{$s}%")
                ->orWhere('job_title', 'like', "%{$s}%"))
            ->when($request->status !== null && $request->status !== '', fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('masters.employees.index', compact('employees'));
    }

    public function create()
    {
        return view('masters.employees.create');
    }

    public function store(StoreEmployeeRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $data        = $request->validated();
            $experiences = $data['experiences'] ?? [];
            unset($data['experiences']);

            $employee = Employee::create($data);

            foreach ($experiences as $exp) {
                if (!empty($exp['company_name'])) {
                    $employee->experiences()->create($exp);
                }
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'employee' => $employee]);
            }

            return redirect()->route('masters.employees.index')
                ->with('success', 'Employee created successfully.');
        });
    }

    public function show(Employee $employee)
    {
        $employee->load('attachments', 'experiences');

        $salaryHistory = $employee->salaryRunLines()
            ->with('salaryRun')
            ->orderByDesc('id')
            ->get();

        $loanTransactions = Transaction::where('reference_type', 'Employee')
            ->where('reference_id', $employee->id)
            ->orderByDesc('transaction_date')
            ->get();

        return view('masters.employees.show', compact('employee', 'salaryHistory', 'loanTransactions'));
    }

    public function edit(Employee $employee)
    {
        $employee->load('experiences');
        return view('masters.employees.edit', compact('employee'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        return DB::transaction(function () use ($request, $employee) {
            $data        = $request->validated();
            $experiences = $data['experiences'] ?? [];
            unset($data['experiences']);

            $employee->update($data);

            // Replace experience records
            $employee->experiences()->delete();
            foreach ($experiences as $exp) {
                if (!empty($exp['company_name'])) {
                    $employee->experiences()->create($exp);
                }
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'employee' => $employee]);
            }

            return redirect()->route('masters.employees.index')
                ->with('success', 'Employee updated successfully.');
        });
    }

    public function exportPdf(Request $request)
    {
        $employees = Employee::query()
            ->when($request->search, fn ($q, $s) => $q->where('employee_name', 'like', "%{$s}%"))
            ->when($request->status !== null && $request->status !== '', fn ($q) => $q->where('status', $request->status))
            ->orderBy('employee_name')
            ->get();

        $pdf = Pdf::loadView('exports.employees-list-pdf', compact('employees'))
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download('Employees-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportSinglePdf(Employee $employee)
    {
        $employee->load('experiences');

        $salaryHistory = $employee->salaryRunLines()
            ->with('salaryRun')
            ->orderByDesc('id')
            ->get();

        $pdf = Pdf::loadView('exports.employee-profile-pdf', compact('employee', 'salaryHistory'))
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->download("Employee-{$employee->employee_name}.pdf");
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('masters.employees.index')
            ->with('success', 'Employee removed successfully.');
    }
}
