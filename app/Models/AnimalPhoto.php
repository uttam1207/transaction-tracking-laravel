<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Animal;

class AnimalPhoto extends Model
{
    protected $fillable = [
        'animal_id',
        'photo_path',
        'caption',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }
}
