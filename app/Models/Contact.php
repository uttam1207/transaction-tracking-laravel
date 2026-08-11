<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    protected $fillable = [
        'contact_category_id',
        'name',
        'phone',
        'alternate_phone',
        'email',
        'company',
        'designation',
        'address',
        'city',
        'state',
        'pincode',
        'notes',
        'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ContactCategory::class, 'contact_category_id');
    }
}