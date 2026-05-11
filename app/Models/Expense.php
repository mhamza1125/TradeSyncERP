<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Expense extends Model
{
    use LogsActivity;

    protected $fillable = [
        'expense_head_id',
        'account_id',
        'transaction_id',
        'amount',
        'expense_date',
        'description',
        'attachment',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function expenseHead()
    {
        return $this->belongsTo(ExpenseHead::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
