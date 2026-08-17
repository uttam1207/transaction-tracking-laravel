<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'entry_number', 'period_id', 'entry_date', 'reference', 'type',
        'description', 'total_debit', 'total_credit', 'status',
        'created_by', 'posted_by', 'posted_at', 'reversal_of',
    ];

    protected $casts = [
        'entry_date'  => 'date',
        'posted_at'   => 'datetime',
        'total_debit' => 'decimal:2',
        'total_credit'=> 'decimal:2',
    ];

    public function period()
    {
        return $this->belongsTo(FinancialPeriod::class, 'period_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function reversalOf()
    {
        return $this->belongsTo(JournalEntry::class, 'reversal_of');
    }

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class, 'journal_entry_id');
    }

    public function isBalanced(): bool
    {
        return (float) $this->total_debit === (float) $this->total_credit;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft'     => 'secondary',
            'posted'    => 'success',
            'reversed'  => 'warning',
            'cancelled' => 'danger',
            default     => 'dark',
        };
    }

    public static function generateNumber(): string
    {
        $year = now()->year;
        $last = static::whereYear('created_at', $year)->count() + 1;
        return 'JE-' . $year . '-' . str_pad($last, 5, '0', STR_PAD_LEFT);
    }
}
