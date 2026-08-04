<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MilkEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'shift',
        'animal_id',
        'quantity_liters',
        'fat_percentage',
        'snf_percentage',
        'quality_rating',
        'rejected_liters',
        'recorded_by',
    ];

    protected $casts = [
        'date' => 'date',
        'quantity_liters' => 'decimal:2',
        'fat_percentage' => 'decimal:2',
        'snf_percentage' => 'decimal:2',
        'rejected_liters' => 'decimal:2',
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}
