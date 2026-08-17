<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $fillable = [
        'code', 'name', 'type', 'sub_type', 'parent_id',
        'description', 'is_active', 'allow_direct_posting',
    ];

    protected $casts = [
        'is_active'            => 'boolean',
        'allow_direct_posting' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    public function journalLines()
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    public function ledgerBalances()
    {
        return $this->hasMany(LedgerBalance::class, 'account_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePostable($query)
    {
        return $query->where('is_active', true)->where('allow_direct_posting', true);
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'asset'     => 'primary',
            'liability' => 'danger',
            'equity'    => 'warning',
            'revenue'   => 'success',
            'expense'   => 'secondary',
            default     => 'dark',
        };
    }
}
