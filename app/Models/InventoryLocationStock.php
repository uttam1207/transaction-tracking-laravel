<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryLocationStock extends Model
{
    protected $table = 'inventory_location_stock';

    protected $fillable = [
        'inventory_item_id', 'warehouse_id', 'location_id', 'quantity', 'reserved_qty',
    ];

    protected $casts = [
        'quantity'     => 'decimal:3',
        'reserved_qty' => 'decimal:3',
    ];

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function location()
    {
        return $this->belongsTo(WarehouseLocation::class, 'location_id');
    }

    public function getAvailableQtyAttribute(): float
    {
        return (float) $this->quantity - (float) $this->reserved_qty;
    }
}
