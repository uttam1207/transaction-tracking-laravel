<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Franchise extends Model
{
    use HasFactory;

    protected $fillable = [
        'franchise_code',
        'owner_name',
        'location',
        'contact_number',
        'agreement_date',
        'investment_amount',
        'status',
        'royalty_percentage',
        'milk_collected_liters',
    ];

    protected $casts = [
        'agreement_date' => 'date',
        'investment_amount' => 'decimal:2',
        'royalty_percentage' => 'decimal:2',
        'milk_collected_liters' => 'decimal:2',
    ];
}
