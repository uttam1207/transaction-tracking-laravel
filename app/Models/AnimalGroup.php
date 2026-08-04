<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimalGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_key',
        'name',
        'head_count',
    ];

    public function feedPlans()
    {
        return $this->hasMany(FeedPlan::class);
    }
}
