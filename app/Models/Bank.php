<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Bank extends Model
{
    use LogsActivity;

    protected $fillable = [
        'bank_name',
        'branch_name',
        'account_number',
        'swift_code',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
}
