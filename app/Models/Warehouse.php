<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = [
        'code', 'name', 'address', 'city', 'country', 'manager_id', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function locations()
    {
        return $this->hasMany(WarehouseLocation::class, 'warehouse_id');
    }

    public function locationStock()
    {
        return $this->hasMany(InventoryLocationStock::class, 'warehouse_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
