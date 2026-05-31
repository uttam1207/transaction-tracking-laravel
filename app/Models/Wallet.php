<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
            $before = (float) $this->balance;
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

    public static function findOrCreateForUser(int $userId): self
    {
        return static::firstOrCreate(['user_id' => $userId], [
            'balance'  => 0.00,
            'currency' => 'INR',
            'status'   => 'active',
        ]);
    }
}
