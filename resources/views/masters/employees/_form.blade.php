{{-- Reusable employee form partial --}}
<div class="row">
    <div class="col-xl-8">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Employee Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Employee Name <span class="text-danger">*</span></label>
                        <input type="text" name="employee_name" class="form-control @error('employee_name') is-invalid @enderror"
                               placeholder="Full name" value="{{ old('employee_name', $employee->employee_name ?? '') }}">
                        @error('employee_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                               placeholder="+92 300 0000000" value="{{ old('phone', $employee->phone ?? '') }}">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Department <span class="text-danger">*</span></label>
                        <input type="text" name="department" class="form-control @error('department') is-invalid @enderror"
                               placeholder="e.g. Operations, Finance" value="{{ old('department', $employee->department ?? '') }}">
                        @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Designation <span class="text-danger">*</span></label>
                        <input type="text" name="designation" class="form-control @error('designation') is-invalid @enderror"
                               placeholder="e.g. Manager, Analyst" value="{{ old('designation', $employee->designation ?? '') }}">
                        @error('designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Joining Date <span class="text-danger">*</span></label>
                        <input type="date" name="joining_date" class="form-control @error('joining_date') is-invalid @enderror"
                               value="{{ old('joining_date', isset($employee) ? $employee->joining_date : '') }}">
                        @error('joining_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Basic Salary <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="basic_salary" class="form-control @error('basic_salary') is-invalid @enderror"
                               placeholder="0.00" value="{{ old('basic_salary', $employee->basic_salary ?? '') }}">
                        @error('basic_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card stretch stretch-full">
            <div class="card-header">
                <h5 class="card-title">Status</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">Employment Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="1" @selected(old('status', $employee->status ?? 1) == 1)>Active</option>
                        <option value="0" @selected(old('status', $employee->status ?? 1) == 0)>Inactive</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>
