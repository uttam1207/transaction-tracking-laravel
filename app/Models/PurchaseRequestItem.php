<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    protected $fillable = [
        'purchase_request_id', 'item_name', 'description',
        'quantity', 'unit', 'estimated_unit_price', 'estimated_total', 'inventory_item_id',
    ];

    protected $casts = [
        'quantity'              => 'decimal:2',
        'estimated_unit_price'  => 'decimal:2',
        'estimated_total'       => 'decimal:2',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}
