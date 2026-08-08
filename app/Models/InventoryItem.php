<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StockMovement;
use Carbon\Carbon;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'item_type',
        'unit',
        'min_stock',
        'expiry_date',
        'description',
        'is_active',
    ];

    protected $casts = [
        'min_stock'   => 'decimal:2',
        'is_active'   => 'boolean',
        'expiry_date' => 'date',
    ];

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getAvailableQuantityAttribute(): float
    {
        $stockIn = $this->stockMovements()->where('type', 'in')->sum('quantity');
        $stockOut = $this->stockMovements()->where('type', 'out')->sum('quantity');

        $adjustmentsIncrease = $this->stockMovements()
            ->where('type', 'adjustment')
            ->where('reason', 'like', '%Increase%')
            ->sum('quantity');

        $adjustmentsDecrease = $this->stockMovements()
            ->where('type', 'adjustment')
            ->where('reason', 'like', '%Decrease%')
            ->sum('quantity');

        return (float) max(0, ($stockIn + $adjustmentsIncrease) - ($stockOut + $adjustmentsDecrease));
    }

    public function getStockStatusAttribute(): string
    {
        $available = $this->available_quantity;
        if ($available <= 0) {
            return 'Out of Stock';
        }
        if ($available <= $this->min_stock) {
            return 'Low Stock';
        }
        return 'Optimal';
    }

    public function getStockStatusBadgeAttribute(): string
    {
        return match ($this->stock_status) {
            'Out of Stock' => 'bg-danger',
            'Low Stock'    => 'bg-warning text-dark',
            default        => 'bg-success',
        };
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expiry_date
            && !$this->expiry_date->isPast()
            && $this->expiry_date->diffInDays(now()) <= 30;
    }

    public function getDaysToExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) return null;
        if ($this->expiry_date->isPast()) return 0;
        return (int) now()->diffInDays($this->expiry_date);
    }
}
