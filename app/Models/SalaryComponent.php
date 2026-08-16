<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'type', 'calculation_type', 'default_value',
        'is_mandatory', 'is_taxable', 'sort_order', 'is_active', 'description',
    ];

    protected $casts = [
        'is_mandatory'  => 'boolean',
        'is_taxable'    => 'boolean',
        'is_active'     => 'boolean',
        'default_value' => 'decimal:2',
        'sort_order'    => 'integer',
    ];

    public function structureItems()
    {
        return $this->hasMany(SalaryStructureItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEarnings($query)
    {
        return $query->where('type', 'earning');
    }

    public function scopeDeductions($query)
    {
        return $query->where('type', 'deduction');
    }

    /**
     * Calculate this component's value given a basic salary.
     */
    public function calculate(float $basic, float $gross = 0): float
    {
        $val = (float) $this->default_value;
        return match ($this->calculation_type) {
            'percentage_of_basic' => round($basic * $val / 100, 2),
            'percentage_of_gross' => round($gross * $val / 100, 2),
            default               => $val, // fixed
        };
    }
}
