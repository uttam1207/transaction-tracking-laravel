<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'plot_name',
        'crop_type',
        'plantation_date',
        'fertilizer_used',
        'pesticide_used',
        'harvest_date',
        'yield_kg',
        'diesel_liters',
        'electricity_units',
        'water_usage_liters',
    ];

    protected $casts = [
        'plantation_date' => 'date',
        'harvest_date' => 'date',
        'yield_kg' => 'decimal:2',
        'diesel_liters' => 'decimal:2',
        'electricity_units' => 'decimal:2',
        'water_usage_liters' => 'decimal:2',
    ];
}
