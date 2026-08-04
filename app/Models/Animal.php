<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    use HasFactory;

    protected $fillable = [
        'tag_number',
        'name',
        'breed',
        'dob',
        'purchase_date',
        'purchase_cost',
        'current_weight',
        'lactation_number',
        'health_status',
        'pregnancy_status',
        'owner_name',
        'shed_number',
        'status',
    ];

    protected $casts = [
        'dob' => 'date',
        'purchase_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'current_weight' => 'decimal:2',
    ];

    public function actions()
    {
        return $this->hasMany(AnimalAction::class);
    }

    public function milkEntries()
    {
        return $this->hasMany(MilkEntry::class);
    }

    public function breedingRecords()
    {
        return $this->hasMany(BreedingRecord::class);
    }

    public function healthRecords()
    {
        return $this->hasMany(HealthRecord::class);
    }
}
