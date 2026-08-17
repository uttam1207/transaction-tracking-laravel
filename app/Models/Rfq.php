<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rfq extends Model
{
    protected $fillable = [
        'rfq_number', 'purchase_request_id', 'due_date',
        'terms_conditions', 'notes', 'status', 'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rfqVendors()
    {
        return $this->hasMany(RfqVendor::class, 'rfq_id');
    }

    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'rfq_vendors')->withPivot('sent_at', 'responded_at')->withTimestamps();
    }

    public function quotations()
    {
        return $this->hasMany(VendorQuotation::class, 'rfq_id');
    }

    public static function generateNumber(): string
    {
        $year = now()->year;
        $last = static::whereYear('created_at', $year)->count() + 1;
        return 'RFQ-' . $year . '-' . str_pad($last, 5, '0', STR_PAD_LEFT);
    }
}
