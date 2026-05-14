<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ExpenseHead extends Model
{
    use LogsActivity;

    protected $fillable = [
        'parent_id',
        'expense_name',
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

    public function parent()
    {
        return $this->belongsTo(ExpenseHead::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ExpenseHead::class, 'parent_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function isCategory(): bool
    {
        return is_null($this->parent_id);
    }

    public function isSubcategory(): bool
    {
        return !is_null($this->parent_id);
    }
}
