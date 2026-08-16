<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AnimalGroup;
use App\Models\InventoryItem;

class FeedPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_group_id',
        'inventory_item_id',  // Phase 1: normalised FK
        'feed_item_name',     // Legacy: kept for backward compat
        'quantity_per_animal_kg',
        'is_active',
    ];

    protected $casts = [
        'quantity_per_animal_kg' => 'decimal:2',
        'is_active'              => 'boolean',
    ];

    public function animalGroup()
    {
        return $this->belongsTo(AnimalGroup::class);
    }

    /** Phase 1: direct FK to inventory. */
    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Resolve the effective inventory item — prefer FK, fall back to name match.
     * Returns null if not found.
     */
    public function resolveInventoryItem(): ?InventoryItem
    {
        if ($this->inventory_item_id) {
            return $this->inventoryItem;
        }
        if (!$this->feed_item_name) {
            return null;
        }
        return InventoryItem::where('name', $this->feed_item_name)->first()
            ?? InventoryItem::where('name', 'like', "%{$this->feed_item_name}%")->first()
            ?? InventoryItem::where('name', 'like', '%' . strtok($this->feed_item_name, ' ') . '%')->first();
    }

    /** The display name: use inventory item name if FK resolved, else legacy string. */
    public function getEffectiveFeedNameAttribute(): string
    {
        return $this->inventoryItem?->name ?? $this->feed_item_name ?? '—';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
