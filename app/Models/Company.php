<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'code', 'logo', 'tagline', 'address', 'city', 'state',
        'country', 'postal_code', 'phone', 'email', 'website',
        'gst_number', 'pan_number', 'cin_number',
        'currency', 'timezone', 'financial_year_start',
        'is_active', 'description',
    ];

    protected $casts = [
        'is_active'             => 'boolean',
        'financial_year_start'  => 'integer',
    ];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function activeBranches()
    {
        return $this->hasMany(Branch::class)->where('is_active', true);
    }

    public function headquarters()
    {
        return $this->hasOne(Branch::class)->where('is_headquarters', true);
    }

    public static function main(): self
    {
        return static::firstOrCreate(
            ['code' => 'ASDAIRY'],
            [
                'name'     => 'ASDairy',
                'currency' => 'INR',
                'timezone' => 'Asia/Kolkata',
                'is_active'=> true,
            ]
        );
    }

    public function getLogoUrlAttribute(): string
    {
        if (!$this->logo) {
            return asset('images/logo.jpeg');
        }
        return str_starts_with($this->logo, 'uploads/')
            ? asset($this->logo)
            : asset('storage/' . $this->logo);
    }
}
