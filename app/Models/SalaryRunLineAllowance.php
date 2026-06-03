<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryRunLineAllowance extends Model
{
    protected $fillable = ['salary_run_line_id', 'allowance_type_id', 'amount'];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function salaryRunLine()
    {
        return $this->belongsTo(SalaryRunLine::class);
    }

    public function allowanceType()
    {
        return $this->belongsTo(AllowanceType::class);
    }
}
