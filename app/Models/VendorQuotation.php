<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorQuotation extends Model
{
    protected $fillable = [
        'quotation_number', 'rfq_id', 'vendor_id', 'quotation_date', 'valid_until',
        'total_amount', 'tax_amount', 'grand_total', 'status', 'notes', 'attachment', 'created_by',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until'    => 'date',
        'total_amount'   => 'decimal:2',
        'tax_amount'     => 'decimal:2',
        'grand_total'    => 'decimal:2',
    ];

    public function rfq()
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(VendorQuotationItem::class, 'vendor_quotation_id');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'received'    => 'secondary',
            'shortlisted' => 'info',
            'selected'    => 'success',
            'rejected'    => 'danger',
            default       => 'dark',
        };
    }

    public static function generateNumber(): string
    {
        $year = now()->year;
        $last = static::whereYear('created_at', $year)->count() + 1;
        return 'QT-' . $year . '-' . str_pad($last, 5, '0', STR_PAD_LEFT);
    }
}
