<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'crm_customer_id',
        'item_type',
        'sale_date',
        'quantity',
        'rate',
        'total_amount',
        'payment_status',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'quantity' => 'decimal:2',
        'rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(CrmCustomer::class, 'crm_customer_id');
    }
}
