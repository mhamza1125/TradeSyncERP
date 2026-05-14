<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Employee extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'employee_name',
        'father_name',
        'department',
        'designation',
        'job_title',
        'phone',
        'nic',
        'dob',
        'gender',
        'marital_status',
        'emergency_contact',
        'address',
        'city',
        'country',
        'postal_code',
        'joining_date',
        'hire_date',
        'basic_salary',
        'salary',
        'status',
        'remarks',
    ];

    protected $casts = [
        'dob'          => 'date',
        'joining_date' => 'date',
        'hire_date'    => 'date',
        'basic_salary' => 'decimal:2',
        'salary'       => 'decimal:2',
        'status'       => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function experiences()
    {
        return $this->hasMany(EmployeeExperience::class);
    }

    public function salaryRunLines()
    {
        return $this->hasMany(SalaryRunLine::class);
    }

    public function inspections()
    {
        return $this->belongsToMany(Inspection::class, 'employee_inspection');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
