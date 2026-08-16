<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AnimalGroup;
use App\Models\AnimalAction;
use App\Models\MilkEntry;
use App\Models\BreedingRecord;
use App\Models\HealthRecord;
use App\Models\AnimalPhoto;

class Animal extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_id',
        'tag_number',
        'rfid',
        'animal_type',
        'name',
        'breed',
        'group_id',       // Phase 1: normalised FK to animal_groups
        'dob',
        'born_in_farm',
        'purchase_from',
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
        'dob'            => 'date',
        'purchase_date'  => 'date',
        'purchase_cost'  => 'decimal:2',
        'current_weight' => 'decimal:2',
        'born_in_farm'   => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    /** Phase 1: direct group FK. */
    public function group()
    {
        return $this->belongsTo(AnimalGroup::class, 'group_id');
    }

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

    public function photos()
    {
        return $this->hasMany(AnimalPhoto::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeMilking($query)
    {
        return $query->where('status', 'Active')
                     ->where('pregnancy_status', '!=', 'Dry');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getTodayMilkAttribute(): float
    {
        return (float) $this->milkEntries()->whereDate('date', today())->sum('quantity_liters');
    }
}
