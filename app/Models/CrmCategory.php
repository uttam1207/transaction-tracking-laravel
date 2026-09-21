<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmCategory extends Model
{
    protected $fillable = ['name', 'icon', 'color', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function customers(): HasMany
    {
        return $this->hasMany(CrmCustomer::class, 'category', 'name');
    }
}