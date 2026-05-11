<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryRunLine extends Model
{
    protected $fillable = [
        'salary_run_id',
        'employee_id',
        'basic_salary',
        'bonus',
        'deduction',
        'advance',
        'remarks',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'bonus'        => 'decimal:2',
        'deduction'    => 'decimal:2',
        'advance'      => 'decimal:2',
        'net_payable'  => 'decimal:2',
    ];

    public function salaryRun()
    {
        return $this->belongsTo(SalaryRun::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
