<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\AllowanceType;
use Illuminate\Http\Request;

class AllowanceTypeController extends Controller
{
    public function index()
    {
        $allowanceTypes = AllowanceType::withCount('lineAllowances')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('finance.allowance-types.index', compact('allowanceTypes'));
    }

    public function create()
    {
        return view('finance.allowance-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:allowance_types,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active'   => ['boolean'],
        ]);

        AllowanceType::create([
            'name'        => $request->name,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('allowance-types.index')
            ->with('success', 'Allowance type created successfully.');
    }

    public function edit(AllowanceType $allowanceType)
    {
        return view('finance.allowance-types.edit', compact('allowanceType'));
    }

    public function update(Request $request, AllowanceType $allowanceType)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:allowance_types,name,' . $allowanceType->id],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active'   => ['boolean'],
        ]);

        $allowanceType->update([
            'name'        => $request->name,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('allowance-types.index')
            ->with('success', 'Allowance type updated successfully.');
    }

    public function destroy(AllowanceType $allowanceType)
    {
        $allowanceType->update(['is_active' => false]);

        return redirect()->route('allowance-types.index')
            ->with('success', 'Allowance type deactivated.');
    }
}
