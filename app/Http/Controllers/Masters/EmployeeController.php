<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\StoreEmployeeRequest;
use App\Http\Requests\Masters\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:employees.index')->only(['index', 'show']);
        $this->middleware('permission:employees.create')->only(['create', 'store']);
        $this->middleware('permission:employees.edit')->only(['edit', 'update']);
        $this->middleware('permission:employees.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $employees = Employee::query()
            ->when($request->search, fn ($q, $s) => $q->where('employee_name', 'like', "%{$s}%")
                ->orWhere('department', 'like', "%{$s}%"))
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
        $employee = Employee::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'employee' => $employee]);
        }

        return redirect()->route('masters.employees.index')
            ->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee)
    {
        return view('masters.employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        return view('masters.employees.edit', compact('employee'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $employee->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'employee' => $employee]);
        }

        return redirect()->route('masters.employees.index')
            ->with('success', 'Employee updated successfully.');
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
