<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'level', 'description', 'is_active'];

    protected $casts = [
        'level'     => 'integer',
        'is_active' => 'boolean',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getLevelLabelAttribute(): string
    {
        return match($this->level) {
            1 => 'Executive',
            2 => 'Senior',
            3 => 'Mid-Level',
            4 => 'Junior',
            default => 'Mid-Level',
        };
    }

    public function getLevelColorAttribute(): string
    {
        return match($this->level) {
            1 => 'danger',
            2 => 'warning',
            3 => 'info',
            4 => 'secondary',
            default => 'secondary',
        };
    }
}
