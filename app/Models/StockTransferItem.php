<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransferItem extends Model
{
    protected $fillable = [
        'stock_transfer_id', 'inventory_item_id', 'quantity', 'received_qty', 'remarks',
    ];

    protected $casts = [
        'quantity'     => 'decimal:3',
        'received_qty' => 'decimal:3',
    ];

    public function stockTransfer()
    {
        return $this->belongsTo(StockTransfer::class, 'stock_transfer_id');
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}
