<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ServicePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_key', 'service_name', 'description', 'icon',
        'allowed_roles', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'allowed_roles' => 'array',
        'is_active'     => 'boolean',
    ];

    public static function canAccess(string $serviceKey, User $user): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        $permission = Cache::remember("svc_perm_{$serviceKey}", 300, function () use ($serviceKey) {
            return static::where('service_key', $serviceKey)->where('is_active', true)->first();
        });

        if (!$permission) {
            return false;
        }

        return in_array($user->role, $permission->allowed_roles ?? []);
    }

    public static function clearCache(string $serviceKey): void
    {
        Cache::forget("svc_perm_{$serviceKey}");
    }
}
