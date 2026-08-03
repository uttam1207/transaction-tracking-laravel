<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'expense_date', 'expense_category_id', 'description', 'amount',
        'vendor_payee', 'payment_method', 'payment_status', 'reference_number',
        'bill_path', 'remarks', 'recorded_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'cash'          => 'Cash',
            'upi'           => 'UPI',
            'bank_transfer' => 'Bank Transfer',
            'cheque'        => 'Cheque',
            default         => ucfirst($this->payment_method),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->payment_status === 'paid' ? 'success' : 'warning';
    }
}
