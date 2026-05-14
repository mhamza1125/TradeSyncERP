{{-- Reusable employee form partial --}}
<div class="row">
    {{-- Personal Information --}}
    <div class="col-xl-8">
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">Personal Information</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Employee Name <span class="text-danger">*</span></label>
                        <input type="text" name="employee_name" class="form-control @error('employee_name') is-invalid @enderror"
                               placeholder="Full name" value="{{ old('employee_name', $employee->employee_name ?? '') }}">
                        @error('employee_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Father Name</label>
                        <input type="text" name="father_name" class="form-control @error('father_name') is-invalid @enderror"
                               value="{{ old('father_name', $employee->father_name ?? '') }}">
                        @error('father_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                               placeholder="+92 300 0000000" value="{{ old('phone', $employee->phone ?? '') }}">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">NIC</label>
                        <input type="text" name="nic" class="form-control @error('nic') is-invalid @enderror"
                               placeholder="35202-1234567-1" value="{{ old('nic', $employee->nic ?? '') }}">
                        @error('nic')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-4 mb-4">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror"
                               value="{{ old('dob', isset($employee) && $employee->dob ? $employee->dob->toDateString() : '') }}">
                        @error('dob')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-4 mb-4">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                            <option value="">— Select —</option>
                            @foreach(['Male','Female','Other'] as $g)
                            <option value="{{ $g }}" @selected(old('gender', $employee->gender ?? '') === $g)>{{ $g }}</option>
                            @endforeach
                        </select>
                        @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-4 mb-4">
                        <label class="form-label">Marital Status</label>
                        <select name="marital_status" class="form-select @error('marital_status') is-invalid @enderror">
                            <option value="">— Select —</option>
                            @foreach(['Single','Married','Divorced','Widowed'] as $ms)
                            <option value="{{ $ms }}" @selected(old('marital_status', $employee->marital_status ?? '') === $ms)>{{ $ms }}</option>
                            @endforeach
                        </select>
                        @error('marital_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Emergency Contact</label>
                        <input type="text" name="emergency_contact" class="form-control @error('emergency_contact') is-invalid @enderror"
                               placeholder="+92 300 0000000" value="{{ old('emergency_contact', $employee->emergency_contact ?? '') }}">
                        @error('emergency_contact')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 mb-4">
                        <label class="form-label">Address</label>
                        <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror">{{ old('address', $employee->address ?? '') }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-4 mb-4">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                               value="{{ old('city', $employee->city ?? '') }}">
                        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-4 mb-4">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-control @error('country') is-invalid @enderror"
                               value="{{ old('country', $employee->country ?? '') }}">
                        @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-4 mb-4">
                        <label class="form-label">Postal Code</label>
                        <input type="text" name="postal_code" class="form-control @error('postal_code') is-invalid @enderror"
                               value="{{ old('postal_code', $employee->postal_code ?? '') }}">
                        @error('postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Employment Details --}}
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title">Employment Details</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Department</label>
                        <input type="text" name="department" class="form-control @error('department') is-invalid @enderror"
                               placeholder="e.g. Operations, Finance" value="{{ old('department', $employee->department ?? '') }}">
                        @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Designation</label>
                        <input type="text" name="designation" class="form-control @error('designation') is-invalid @enderror"
                               placeholder="e.g. Manager" value="{{ old('designation', $employee->designation ?? '') }}">
                        @error('designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Job Title</label>
                        <input type="text" name="job_title" class="form-control @error('job_title') is-invalid @enderror"
                               placeholder="e.g. Quality Inspector" value="{{ old('job_title', $employee->job_title ?? '') }}">
                        @error('job_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Hire Date</label>
                        <input type="date" name="hire_date" class="form-control @error('hire_date') is-invalid @enderror"
                               value="{{ old('hire_date', isset($employee) && $employee->hire_date ? $employee->hire_date->toDateString() : '') }}">
                        @error('hire_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Joining Date</label>
                        <input type="date" name="joining_date" class="form-control @error('joining_date') is-invalid @enderror"
                               value="{{ old('joining_date', isset($employee) && $employee->joining_date ? $employee->joining_date->toDateString() : '') }}">
                        @error('joining_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Salary</label>
                        <input type="number" step="0.01" name="salary" class="form-control @error('salary') is-invalid @enderror"
                               placeholder="0.00" value="{{ old('salary', $employee->salary ?? '') }}">
                        @error('salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label">Basic Salary (for payroll)</label>
                        <input type="number" step="0.01" name="basic_salary" class="form-control @error('basic_salary') is-invalid @enderror"
                               placeholder="0.00" value="{{ old('basic_salary', $employee->basic_salary ?? '') }}">
                        @error('basic_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 mb-2">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" rows="2" class="form-control @error('remarks') is-invalid @enderror"
                                  placeholder="Optional notes…">{{ old('remarks', $employee->remarks ?? '') }}</textarea>
                        @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Experience Records --}}
        <div class="card" id="experienceCard">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Work Experience</h5>
                <button type="button" class="btn btn-sm btn-light-brand" id="addExperience">
                    <i class="feather-plus me-1"></i> Add
                </button>
            </div>
            <div class="card-body" id="experienceRows">
                @php $experiences = old('experiences', isset($employee) ? $employee->experiences->toArray() : []); @endphp
                @foreach($experiences as $idx => $exp)
                <div class="experience-row border rounded p-3 mb-3">
                    <div class="row">
                        <div class="col-lg-6 mb-3">
                            <label class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="experiences[{{ $idx }}][company_name]" class="form-control"
                                   value="{{ $exp['company_name'] ?? '' }}" placeholder="Company name">
                        </div>
                        <div class="col-lg-6 mb-3">
                            <label class="form-label">Designation</label>
                            <input type="text" name="experiences[{{ $idx }}][designation]" class="form-control"
                                   value="{{ $exp['designation'] ?? '' }}" placeholder="Role / title">
                        </div>
                        <div class="col-lg-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="experiences[{{ $idx }}][start_date]" class="form-control"
                                   value="{{ $exp['start_date'] ?? '' }}">
                        </div>
                        <div class="col-lg-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="experiences[{{ $idx }}][end_date]" class="form-control"
                                   value="{{ $exp['end_date'] ?? '' }}">
                        </div>
                        <div class="col-12 mb-2">
                            <label class="form-label">Responsibilities</label>
                            <textarea name="experiences[{{ $idx }}][responsibilities]" rows="2" class="form-control"
                                      placeholder="Key responsibilities…">{{ $exp['responsibilities'] ?? '' }}</textarea>
                        </div>
                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-xs btn-light-danger remove-experience">
                                <i class="feather-trash-2 me-1"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
                @if(empty($experiences))
                <p class="text-muted text-center py-3 mb-0" id="noExperienceMsg">No work experience added yet.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title">Settings</h5></div>
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

@push('scripts')
<script>
let expIdx = {{ count($experiences ?? []) }};

document.getElementById('addExperience').addEventListener('click', function () {
    const noMsg = document.getElementById('noExperienceMsg');
    if (noMsg) noMsg.remove();

    const row = document.createElement('div');
    row.className = 'experience-row border rounded p-3 mb-3';
    row.innerHTML = `
        <div class="row">
            <div class="col-lg-6 mb-3">
                <label class="form-label">Company Name <span class="text-danger">*</span></label>
                <input type="text" name="experiences[${expIdx}][company_name]" class="form-control" placeholder="Company name">
            </div>
            <div class="col-lg-6 mb-3">
                <label class="form-label">Designation</label>
                <input type="text" name="experiences[${expIdx}][designation]" class="form-control" placeholder="Role / title">
            </div>
            <div class="col-lg-6 mb-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="experiences[${expIdx}][start_date]" class="form-control">
            </div>
            <div class="col-lg-6 mb-3">
                <label class="form-label">End Date</label>
                <input type="date" name="experiences[${expIdx}][end_date]" class="form-control">
            </div>
            <div class="col-12 mb-2">
                <label class="form-label">Responsibilities</label>
                <textarea name="experiences[${expIdx}][responsibilities]" rows="2" class="form-control" placeholder="Key responsibilities…"></textarea>
            </div>
            <div class="col-12 text-end">
                <button type="button" class="btn btn-xs btn-light-danger remove-experience">
                    <i class="feather-trash-2 me-1"></i> Remove
                </button>
            </div>
        </div>`;
    document.getElementById('experienceRows').appendChild(row);
    expIdx++;
    row.querySelector('.remove-experience').addEventListener('click', () => row.remove());
});

document.querySelectorAll('.remove-experience').forEach(btn => {
    btn.addEventListener('click', () => btn.closest('.experience-row').remove());
});
</script>
@endpush
