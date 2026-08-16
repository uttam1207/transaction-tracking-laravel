<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryStructureItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_structure_id', 'salary_component_id', 'value',
    ];

    protected $casts = [
        'value' => 'decimal:2',
    ];

    public function structure()
    {
        return $this->belongsTo(SalaryStructure::class, 'salary_structure_id');
    }

    public function component()
    {
        return $this->belongsTo(SalaryComponent::class, 'salary_component_id');
    }

    /**
     * Resolve the actual monetary value of this item given a basic salary.
     */
    public function resolveValue(float $basic, float $gross = 0): float
    {
        $v = (float) $this->value;
        return match ($this->component?->calculation_type) {
            'percentage_of_basic' => round($basic * $v / 100, 2),
            'percentage_of_gross' => round($gross * $v / 100, 2),
            default               => $v,
        };
    }
}
