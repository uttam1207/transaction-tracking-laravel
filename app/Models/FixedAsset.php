<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FixedAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_code',
        'name',
        'asset_type',
        'description',
        'serial_number',
        'location',
        'vendor',
        'purchase_date',
        'purchase_value',
        'salvage_value',
        'useful_life_years',
        'depreciation_method',
        'depreciation_rate',
        'status',
        'disposal_date',
        'disposal_value',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'purchase_date'  => 'date',
        'disposal_date'  => 'date',
        'purchase_value' => 'decimal:2',
        'salvage_value'  => 'decimal:2',
        'disposal_value' => 'decimal:2',
        'depreciation_rate' => 'decimal:2',
    ];

    // ── Constants ─────────────────────────────────────────────────────────────

    public const ASSET_TYPES = [
        'Land'      => ['icon' => 'map',            'color' => '#059669', 'bg' => '#d1fae5', 'prefix' => 'LND'],
        'Building'  => ['icon' => 'building',       'color' => '#2563eb', 'bg' => '#dbeafe', 'prefix' => 'BLD'],
        'Vehicle'   => ['icon' => 'truck',          'color' => '#7c3aed', 'bg' => '#ede9fe', 'prefix' => 'VEH'],
        'Machinery' => ['icon' => 'gear-wide',      'color' => '#d97706', 'bg' => '#fef3c7', 'prefix' => 'MCH'],
        'Equipment' => ['icon' => 'tools',          'color' => '#0891b2', 'bg' => '#cffafe', 'prefix' => 'EQP'],
        'Furniture' => ['icon' => 'house',          'color' => '#be185d', 'bg' => '#fce7f3', 'prefix' => 'FRN'],
        'Computer'  => ['icon' => 'pc-display',     'color' => '#1d4ed8', 'bg' => '#eff6ff', 'prefix' => 'CPU'],
        'Other'     => ['icon' => 'box-seam',       'color' => '#6b7280', 'bg' => '#f3f4f6', 'prefix' => 'OTH'],
    ];

    public const STATUSES = [
        'active'            => ['label' => 'Active',            'color' => '#059669', 'bg' => '#d1fae5'],
        'under_maintenance' => ['label' => 'Under Maintenance', 'color' => '#d97706', 'bg' => '#fef3c7'],
        'disposed'          => ['label' => 'Disposed',          'color' => '#dc2626', 'bg' => '#fee2e2'],
        'written_off'       => ['label' => 'Written Off',       'color' => '#6b7280', 'bg' => '#f3f4f6'],
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Accessors / Computed ──────────────────────────────────────────────────

    /** Full years elapsed since purchase date */
    public function getYearsElapsedAttribute(): float
    {
        $start = $this->purchase_date ?? now();
        $end   = ($this->status === 'disposed' && $this->disposal_date) ? $this->disposal_date : now();
        return max(0, round($start->diffInDays($end) / 365.25, 4));
    }

    /** Annual depreciation amount (straight-line) */
    public function getAnnualDepreciationAttribute(): float
    {
        if ($this->depreciation_method === 'none') return 0;
        $depreciable = (float) $this->purchase_value - (float) $this->salvage_value;
        if ($this->depreciation_method === 'straight_line') {
            return $this->useful_life_years > 0
                ? round($depreciable / $this->useful_life_years, 2)
                : 0;
        }
        // reducing_balance — annual depreciation in year 1 using rate
        $rate = (float) ($this->depreciation_rate ?? 0) / 100;
        return round((float) $this->purchase_value * $rate, 2);
    }

    /** Current book value after depreciation */
    public function getBookValueAttribute(): float
    {
        if ($this->depreciation_method === 'none') {
            return (float) $this->purchase_value;
        }

        $years     = $this->years_elapsed;
        $purchase  = (float) $this->purchase_value;
        $salvage   = (float) $this->salvage_value;
        $lifeYears = max(1, (int) $this->useful_life_years);

        if ($this->depreciation_method === 'straight_line') {
            $totalDepr = min($purchase - $salvage, ($this->annual_depreciation * $years));
            return max($salvage, round($purchase - $totalDepr, 2));
        }

        // Reducing balance
        $rate = (float) ($this->depreciation_rate ?? 0) / 100;
        if ($rate <= 0) return $purchase;
        $bookVal = $purchase * pow(1 - $rate, $years);
        return max($salvage, round($bookVal, 2));
    }

    /** Total accumulated depreciation so far */
    public function getAccumulatedDepreciationAttribute(): float
    {
        return max(0, round((float) $this->purchase_value - $this->book_value, 2));
    }

    /** % of original purchase value that has been depreciated */
    public function getDepreciationPercentAttribute(): float
    {
        $purchase = (float) $this->purchase_value;
        if ($purchase <= 0) return 0;
        return min(100, round(($this->accumulated_depreciation / $purchase) * 100, 1));
    }

    /** Remaining useful life in years */
    public function getRemainingLifeAttribute(): float
    {
        return max(0, round($this->useful_life_years - $this->years_elapsed, 1));
    }

    /** Age string like "3y 4m" */
    public function getAgeStringAttribute(): string
    {
        $start = $this->purchase_date ?? now();
        $years = (int) $start->diffInYears(now());
        $months = (int) $start->copy()->addYears($years)->diffInMonths(now());
        if ($years === 0) return "{$months}m";
        return $months > 0 ? "{$years}y {$months}m" : "{$years}y";
    }

    /** Asset type config array */
    public function getTypeConfigAttribute(): array
    {
        return self::ASSET_TYPES[$this->asset_type] ?? self::ASSET_TYPES['Other'];
    }

    /** Status config array */
    public function getStatusConfigAttribute(): array
    {
        return self::STATUSES[$this->status] ?? self::STATUSES['active'];
    }

    /** Year-by-year depreciation schedule */
    public function getDepreciationScheduleAttribute(): array
    {
        if ($this->depreciation_method === 'none') return [];

        $schedule = [];
        $purchase  = (float) $this->purchase_value;
        $salvage   = (float) $this->salvage_value;
        $life      = max(1, (int) $this->useful_life_years);
        $openingBV = $purchase;

        for ($year = 1; $year <= $life; $year++) {
            if ($this->depreciation_method === 'straight_line') {
                $depr = min($openingBV - $salvage, round(($purchase - $salvage) / $life, 2));
            } else {
                $rate = (float) ($this->depreciation_rate ?? 0) / 100;
                $depr = round($openingBV * $rate, 2);
            }
            $depr = max(0, $depr);
            $closingBV = max($salvage, round($openingBV - $depr, 2));

            $schedule[] = [
                'year'        => $year,
                'opening_bv'  => $openingBV,
                'depreciation'=> $depr,
                'closing_bv'  => $closingBV,
                'is_past'     => $year <= floor($this->years_elapsed),
            ];

            $openingBV = $closingBV;
            if ($closingBV <= $salvage) break;
        }
        return $schedule;
    }

    // ── Static helpers ────────────────────────────────────────────────────────

    public static function generateCode(string $assetType): string
    {
        $prefix = self::ASSET_TYPES[$assetType]['prefix'] ?? 'OTH';
        $count  = self::where('asset_type', $assetType)->count() + 1;
        do {
            $code = 'AST-' . $prefix . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            $count++;
        } while (self::where('asset_code', $code)->exists());
        return $code;
    }
}
