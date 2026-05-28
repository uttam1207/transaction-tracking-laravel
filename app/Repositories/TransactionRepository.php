<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TransactionRepository extends BaseRepository
{
    public function __construct(Transaction $model)
    {
        parent::__construct($model);
    }

    /**
     * Paginate transactions with optional filters.
     */
    public function filter(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->newQuery()->with('user');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('sender_name', 'like', "%{$search}%")
                  ->orWhere('receiver_name', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['is_flagged'])) {
            $query->where('is_flagged', true);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get flagged transactions with high risk scores.
     */
    public function getFlagged(int $limit = 10): Collection
    {
        return $this->newQuery()
            ->where('is_flagged', true)
            ->with('user')
            ->orderByDesc('risk_score')
            ->limit($limit)
            ->get();
    }

    /**
     * Aggregate statistics for the dashboard.
     */
    public function getStatistics(string $period = 'month'): array
    {
        $query = $this->newQuery();

        if ($period === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $query->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
        }

        return [
            'total'          => $query->count(),
            'total_amount'   => (float) $query->sum('amount'),
            'success'        => (clone $query)->where('status', 'success')->count(),
            'pending'        => (clone $query)->where('status', 'pending')->count(),
            'failed'         => (clone $query)->where('status', 'failed')->count(),
            'flagged'        => (clone $query)->where('is_flagged', true)->count(),
            'avg_risk_score' => round((float) $query->avg('risk_score'), 1),
        ];
    }

    /**
     * Find a transaction by its unique transaction_id string.
     */
    public function findByTransactionId(string $transactionId): ?Transaction
    {
        return $this->newQuery()->where('transaction_id', $transactionId)->first();
    }
}
