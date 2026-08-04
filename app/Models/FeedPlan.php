<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'animal_group_id',
        'feed_item_name',
        'quantity_per_animal_kg',
    ];

    protected $casts = [
        'quantity_per_animal_kg' => 'decimal:2',
    ];

    public function animalGroup()
    {
        return $this->belongsTo(AnimalGroup::class);
    }
}
