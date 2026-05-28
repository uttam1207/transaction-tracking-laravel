<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Paginate users with filters.
     */
    public function filter(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->newQuery()->with(['roles', 'department']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['role'])) {
            $query->whereHas('roles', fn($q) => $q->where('name', $filters['role']));
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Find a user by their email address.
     */
    public function findByEmail(string $email): ?User
    {
        return $this->newQuery()->where('email', $email)->first();
    }

    /**
     * Get currently online users.
     */
    public function getOnlineUsers(): Collection
    {
        return $this->newQuery()
            ->where('is_online', true)
            ->with('roles')
            ->orderByDesc('last_seen_at')
            ->get();
    }

    /**
     * Get users by role name.
     */
    public function getByRole(string $role): Collection
    {
        return $this->newQuery()
            ->whereHas('roles', fn($q) => $q->where('name', $role))
            ->where('status', 'active')
            ->get();
    }

    /**
     * Update the user's online/last-seen status.
     */
    public function updateOnlineStatus(int $userId, bool $isOnline): void
    {
        $this->newQuery()->where('id', $userId)->update([
            'is_online'    => $isOnline,
            'last_seen_at' => now(),
        ]);
    }
}
