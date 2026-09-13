<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItemType extends Model
{
    protected $fillable = ['name', 'is_milk_type', 'sort_order', 'is_active'];

    protected $casts = [
        'is_milk_type' => 'boolean',
        'is_active'    => 'boolean',
        'sort_order'   => 'integer',
    ];

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    /** Returns all active types sorted for dropdowns. */
    public static function activeOrdered()
    {
        return static::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}