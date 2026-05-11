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
        'department',
        'designation',
        'phone',
        'joining_date',
        'basic_salary',
        'status',
    ];

    protected $casts = [
        'joining_date'  => 'date',
        'basic_salary'  => 'decimal:2',
        'status'        => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function salaryRunLines()
    {
        return $this->hasMany(SalaryRunLine::class);
    }
}
