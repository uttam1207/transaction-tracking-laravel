<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'name', 'guard_name', 'display_name', 'description',
        'color', 'icon', 'is_system', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Users that have this role
    public function users()
    {
        return User::where('role', $this->name);
    }

    public function getUsersCountAttribute(): int
    {
        return User::where('role', $this->name)->count();
    }

    public function getDisplayLabelAttribute(): string
    {
        return $this->display_name ?: ucwords(str_replace('_', ' ', $this->name));
    }

    public static function allActive()
    {
        return static::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();
    }
}
