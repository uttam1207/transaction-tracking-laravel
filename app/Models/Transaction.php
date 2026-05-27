<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_id', 'user_id', 'category', 'type', 'amount', 'currency',
        'fee', 'net_amount', 'status', 'payment_method', 'sender_name',
        'sender_account', 'sender_bank', 'receiver_name', 'receiver_account',
        'receiver_bank', 'reference', 'description', 'notes', 'metadata',
        'ip_address', 'country', 'device_id', 'is_flagged', 'risk_score',
        'fraud_reason', 'is_refunded', 'refund_transaction_id', 'processed_at',
        'attachments',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'metadata' => 'array',
        'attachments' => 'array',
        'is_flagged' => 'boolean',
        'is_refunded' => 'boolean',
        'processed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->hasMany(TransactionLog::class);
    }

    public function fraudAlerts()
    {
        return $this->hasMany(FraudAlert::class);
    }

    public function refundTransaction()
    {
        return $this->belongsTo(Transaction::class, 'refund_transaction_id');
    }

    public function scopeFlagged($query)
    {
        return $query->where('is_flagged', true);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeHighRisk($query, int $threshold = 70)
    {
        return $query->where('risk_score', '>=', $threshold);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'success' => 'success',
            'pending' => 'warning',
            'processing' => 'info',
            'failed' => 'danger',
            'cancelled' => 'secondary',
            'reversed' => 'dark',
            default => 'secondary',
        };
    }

    public function getRiskLevelAttribute(): string
    {
        return match(true) {
            $this->risk_score >= 80 => 'critical',
            $this->risk_score >= 60 => 'high',
            $this->risk_score >= 40 => 'medium',
            default => 'low',
        };
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($transaction) {
            if (!$transaction->transaction_id) {
                $transaction->transaction_id = 'TXN-' . strtoupper(uniqid());
            }
            $transaction->net_amount = $transaction->amount - $transaction->fee;
        });
    }
}
