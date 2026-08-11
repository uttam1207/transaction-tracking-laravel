<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactCategory extends Model
{
    protected $fillable = ['name', 'icon', 'color', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }
}