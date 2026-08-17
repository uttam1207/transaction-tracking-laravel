<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class RoleDataScope extends Model
{
    protected $fillable = [
        'role_id', 'permission', 'scope_type', 'scope_column', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function role()
    {
        // Spatie role model
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_id');
    }

    /**
     * Resolve the scope for a given user + permission string.
     * Returns ['scope_type' => ..., 'scope_column' => ...] or null if no rule.
     */
    public static function resolveForUser(User $user, string $permission): ?array
    {
        // Use Spatie role names on the user
        $roleNames = $user->getRoleNames()->toArray();

        if (empty($roleNames)) {
            // Fallback: use legacy users.role string
            $roleNames = [$user->role];
        }

        // Find the broadest scope across all user roles
        $scopeOrder = ['GLOBAL', 'COMPANY', 'BRANCH', 'DEPARTMENT', 'TEAM', 'PROJECT', 'OWN'];

        $resolved = null;
        $resolvedOrder = count($scopeOrder); // worst (most restrictive)

        $scopes = Cache::remember("data_scopes_{$permission}", 300, function () use ($permission) {
            return static::where('permission', $permission)
                ->where('is_active', true)
                ->with('role')
                ->get();
        });

        foreach ($scopes as $scope) {
            if (!$scope->role || !in_array($scope->role->name, $roleNames)) {
                continue;
            }
            $order = array_search($scope->scope_type, $scopeOrder);
            if ($order < $resolvedOrder) {
                $resolvedOrder = $order;
                $resolved = [
                    'scope_type'   => $scope->scope_type,
                    'scope_column' => $scope->scope_column,
                ];
            }
        }

        return $resolved;
    }

    public static function clearCache(string $permission = ''): void
    {
        if ($permission) {
            Cache::forget("data_scopes_{$permission}");
            return;
        }
        static::distinct('permission')->pluck('permission')
            ->each(fn($p) => Cache::forget("data_scopes_{$p}"));
    }
}
