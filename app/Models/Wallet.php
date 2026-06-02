<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\WalletTransaction;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'balance', 'currency', 'status'];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function credit(float $amount, string $description, int $performedBy, ?string $reference = null): WalletTransaction
    {
        return DB::transaction(function () use ($amount, $description, $performedBy, $reference) {
            $before = (float) $this->balance;
            $after  = $before + $amount;
            $this->update(['balance' => $after]);

            return $this->transactions()->create([
                'type'           => 'credit',
                'amount'         => $amount,
                'balance_before' => $before,
                'balance_after'  => $after,
                'description'    => $description,
                'reference'      => $reference,
                'performed_by'   => $performedBy,
            ]);
        });
    }

    public function debit(float $amount, string $description, int $performedBy, ?string $reference = null): WalletTransaction
    {
        return DB::transaction(function () use ($amount, $description, $performedBy, $reference) {
            // Refresh inside the transaction to prevent race conditions
            $this->refresh();
            $before = (float) $this->balance;

            if ($before < $amount) {
                throw new \RuntimeException(
                    'Insufficient wallet balance. Available: ₹' . number_format($before, 2) .
                    ', Required: ₹' . number_format($amount, 2)
                );
            }

            $after  = $before - $amount;
            $this->update(['balance' => $after]);

            return $this->transactions()->create([
                'type'           => 'debit',
                'amount'         => $amount,
                'balance_before' => $before,
                'balance_after'  => $after,
                'description'    => $description,
                'reference'      => $reference,
                'performed_by'   => $performedBy,
            ]);
        });
    }

    /** Returns the single company-wide wallet (creates it if missing). */
    public static function company(): self
    {
        return static::firstOrCreate(['user_id' => null], [
            'balance'  => 0.00,
            'currency' => 'INR',
            'status'   => 'active',
        ]);
    }

    /** @deprecated Use Wallet::company() — there is only one company wallet. */
    public static function findOrCreateForUser(int $userId): self
    {
        return static::company();
    }
}
