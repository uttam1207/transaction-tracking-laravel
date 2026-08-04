<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimalAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_id',
        'action_type',
        'action_date',
        'cost',
        'notes',
        'performed_by',
    ];

    protected $casts = [
        'action_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}
