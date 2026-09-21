<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleOrderItem extends Model
{
    protected $fillable = [
        'sales_order_id',
        'sale_item_type_id',
        'item_type',
        'description',
        'quantity',
        'rate',
        'fat_percentage',
        'fat_rate',
        'amount',
        'sort_order',
    ];

    protected $casts = [
        'quantity'       => 'decimal:2',
        'rate'           => 'decimal:2',
        'fat_percentage' => 'decimal:2',
        'fat_rate'       => 'decimal:2',
        'amount'         => 'decimal:2',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function itemType()
    {
        return $this->belongsTo(SaleItemType::class, 'sale_item_type_id');
    }
}