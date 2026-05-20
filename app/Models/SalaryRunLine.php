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
        'allowances',
        'leave_days',
        'leave_deduction_amount',
        'total_leaves',
        'deductible_leaves',
        'loan_balance',
        'loan_deduction',
        'remarks',
    ];

    protected $casts = [
        'basic_salary'           => 'decimal:2',
        'bonus'                  => 'decimal:2',
        'deduction'              => 'decimal:2',
        'advance'                => 'decimal:2',
        'allowances'             => 'decimal:2',
        'leave_deduction_amount' => 'decimal:2',
        'loan_balance'           => 'decimal:2',
        'loan_deduction'         => 'decimal:2',
        'net_payable'            => 'decimal:2',
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
