<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorQuotationItem extends Model
{
    protected $fillable = [
        'vendor_quotation_id', 'item_name', 'quantity', 'unit', 'unit_price', 'total_price', 'remarks',
    ];

    protected $casts = [
        'quantity'    => 'decimal:2',
        'unit_price'  => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function quotation()
    {
        return $this->belongsTo(VendorQuotation::class, 'vendor_quotation_id');
    }
}
