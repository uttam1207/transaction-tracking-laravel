<?php

namespace App\Repositories;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EmployeeRepository extends BaseRepository
{
    public function __construct(Employee $model)
    {
        parent::__construct($model);
    }

    /**
     * Paginate employees with filters.
     */
    public function filter(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->newQuery()->with(['user', 'department']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('employee_id', 'like', "%{$search}%");
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['employment_type'])) {
            $query->where('employment_type', $filters['employment_type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['work_location'])) {
            $query->where('work_location', $filters['work_location']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get employees currently checked in.
     */
    public function getCheckedIn(): Collection
    {
        return $this->newQuery()
            ->where('is_checked_in', true)
            ->with(['user', 'department'])
            ->get();
    }

    /**
     * Get employees in a specific department.
     */
    public function getByDepartment(int $departmentId): Collection
    {
        return $this->newQuery()
            ->where('department_id', $departmentId)
            ->where('status', 'active')
            ->with('user')
            ->get();
    }

    /**
     * Find employee by user ID.
     */
    public function findByUserId(int $userId): ?Employee
    {
        return $this->newQuery()
            ->where('user_id', $userId)
            ->with(['user', 'department'])
            ->first();
    }

    /**
     * Get employees with low performance scores.
     */
    public function getLowPerformers(float $threshold = 50.0, int $limit = 10): Collection
    {
        return $this->newQuery()
            ->where('performance_score', '<', $threshold)
            ->where('status', 'active')
            ->with(['user', 'department'])
            ->orderBy('performance_score')
            ->limit($limit)
            ->get();
    }
}
