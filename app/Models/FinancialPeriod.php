<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialPeriod extends Model
{
    protected $fillable = [
        'name', 'type', 'start_date', 'end_date', 'status', 'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'period_id');
    }

    public function ledgerBalances()
    {
        return $this->hasMany(LedgerBalance::class, 'period_id');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
