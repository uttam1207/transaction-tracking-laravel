<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceiptItem extends Model
{
    protected $fillable = [
        'goods_receipt_id', 'inventory_item_id', 'item_name',
        'ordered_qty', 'received_qty', 'unit', 'unit_price', 'remarks',
    ];

    protected $casts = [
        'ordered_qty'  => 'decimal:2',
        'received_qty' => 'decimal:2',
        'unit_price'   => 'decimal:2',
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class, 'goods_receipt_id');
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}
