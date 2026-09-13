<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CrmCustomer;
use App\Models\SaleItemType;

class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'crm_customer_id',
        'item_type',
        'sale_item_type_id',
        'sale_date',
        'quantity',
        'rate',
        'fat_percentage',
        'fat_rate',
        'total_amount',
        'payment_status',
        'journal_entry_id',
    ];

    protected $casts = [
        'sale_date'      => 'date',
        'quantity'       => 'decimal:2',
        'rate'           => 'decimal:2',
        'fat_percentage' => 'decimal:2',
        'fat_rate'       => 'decimal:2',
        'total_amount'   => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(CrmCustomer::class, 'crm_customer_id');
    }

    public function saleItemType()
    {
        return $this->belongsTo(SaleItemType::class, 'sale_item_type_id');
    }
}