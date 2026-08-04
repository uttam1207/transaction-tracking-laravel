<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreedingRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_id',
        'heat_date',
        'ai_date',
        'bull_semen_code',
        'pregnancy_check_date',
        'is_pregnant',
        'expected_calving_date',
        'actual_calving_date',
        'calf_tag_number',
        'status',
    ];

    protected $casts = [
        'heat_date' => 'date',
        'ai_date' => 'date',
        'pregnancy_check_date' => 'date',
        'expected_calving_date' => 'date',
        'actual_calving_date' => 'date',
        'is_pregnant' => 'boolean',
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}
