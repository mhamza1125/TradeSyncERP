<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Account extends Model
{
    use LogsActivity;

    protected $fillable = [
        'account_name',
        'account_type',
        'bank_id',
        'currency',
        'opening_balance',
        'status',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'status'          => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function debitTransactions()
    {
        return $this->hasMany(Transaction::class, 'debit_account_id');
    }

    public function creditTransactions()
    {
        return $this->hasMany(Transaction::class, 'credit_account_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function salaryRuns()
    {
        return $this->hasMany(SalaryRun::class);
    }

    public function customerPayments()
    {
        return $this->hasMany(CustomerPayment::class);
    }
}
