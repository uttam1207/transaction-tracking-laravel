<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_id',
        'record_type',
        'date',
        'disease_symptoms',
        'treatment_given',
        'medicine_used',
        'vet_doctor_name',
        'body_temp',
        'cost',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'body_temp' => 'decimal:1',
        'cost' => 'decimal:2',
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}
