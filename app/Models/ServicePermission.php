<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

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

    /** Farm-related service keys the farmer role can always access. */
    private const FARMER_SERVICES = [
        'animals', 'breeds', 'milk', 'breeding', 'health',
        'feed', 'farm', 'stock', 'maintenance', 'compliance',
    ];

    public static function canAccess(string $serviceKey, User $user): bool
    {
        if ($user->role === 'super_admin') {
            return true;
        }

        // Farmer role gets access to Animal & Farm services without needing DB records
        if ($user->role === 'farmer' && in_array($serviceKey, self::FARMER_SERVICES)) {
            return true;
        }

        // Cache only the allowed_roles array (not the full model) to avoid
        // Eloquent serialization edge cases with the file/database cache drivers.
        $allowedRoles = Cache::remember("svc_perm_{$serviceKey}", 300, function () use ($serviceKey) {
            $record = static::where('service_key', $serviceKey)->where('is_active', true)->first();
            return $record ? ($record->allowed_roles ?? []) : null;
        });

        if ($allowedRoles === null) {
            return false;
        }

        return in_array($user->role, $allowedRoles);
    }

    public static function clearCache(string $serviceKey = ''): void
    {
        if ($serviceKey) {
            Cache::forget("svc_perm_{$serviceKey}");
            return;
        }
        // Clear cache for all services
        static::pluck('service_key')->each(fn($key) => Cache::forget("svc_perm_{$key}"));
    }
}
