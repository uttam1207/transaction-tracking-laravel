<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id', 'action', 'from_status', 'to_status',
        'performed_by', 'notes', 'data', 'ip_address',
    ];

    protected $casts = ['data' => 'array'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
