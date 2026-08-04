<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'phone',
        'email',
        'address',
        'total_business_value',
        'status',
    ];

    protected $casts = [
        'total_business_value' => 'decimal:2',
    ];

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }
}
