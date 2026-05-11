<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SalaryRun extends Model
{
    use LogsActivity;

    protected $fillable = [
        'month',
        'account_id',
        'total_net_payable',
        'status',
        'payment_date',
        'transaction_id',
        'processed_by',
    ];

    protected $casts = [
        'payment_date'     => 'date',
        'total_net_payable' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function lines()
    {
        return $this->hasMany(SalaryRunLine::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'Paid';
    }
}
