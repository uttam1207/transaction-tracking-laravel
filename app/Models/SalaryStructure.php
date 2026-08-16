<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'employee_id', 'department_id',
        'is_default', 'effective_from', 'is_active',
    ];

    protected $casts = [
        'is_default'     => 'boolean',
        'is_active'      => 'boolean',
        'effective_from' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function items()
    {
        return $this->hasMany(SalaryStructureItem::class)->with('component');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Calculate the full salary breakdown for a given basic salary.
     *
     * Returns: [
     *   'basic'        => float,
     *   'earnings'     => float,   // total earnings including basic
     *   'deductions'   => float,
     *   'gross'        => float,
     *   'net'          => float,
     *   'breakdown'    => [ ['name' => ..., 'type' => ..., 'amount' => ...], ... ]
     * ]
     */
    public function calculateNet(float $basic): array
    {
        $this->loadMissing('items.component');

        $earningsTotal   = $basic;
        $deductionsTotal = 0;
        $breakdown       = [['name' => 'Basic Salary', 'type' => 'earning', 'amount' => $basic]];

        // First pass: earnings (gross needed for percentage_of_gross later)
        $earningItems = $this->items->filter(
            fn($i) => $i->component?->type === 'earning'
        )->sortBy('component.sort_order');

        foreach ($earningItems as $item) {
            $amount       = $item->resolveValue($basic);
            $earningsTotal += $amount;
            $breakdown[]  = ['name' => $item->component->name, 'type' => 'earning', 'amount' => $amount];
        }

        $gross = $earningsTotal;

        // Second pass: deductions (can use gross)
        $deductionItems = $this->items->filter(
            fn($i) => $i->component?->type === 'deduction'
        )->sortBy('component.sort_order');

        foreach ($deductionItems as $item) {
            $amount          = $item->resolveValue($basic, $gross);
            $deductionsTotal += $amount;
            $breakdown[]     = ['name' => $item->component->name, 'type' => 'deduction', 'amount' => $amount];
        }

        return [
            'basic'      => $basic,
            'earnings'   => $earningsTotal,
            'deductions' => $deductionsTotal,
            'gross'      => $gross,
            'net'        => $gross - $deductionsTotal,
            'breakdown'  => $breakdown,
        ];
    }
}
