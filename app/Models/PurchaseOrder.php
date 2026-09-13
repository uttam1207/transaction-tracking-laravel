<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\JournalEntry;
use App\Models\Vendor;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'vendor_id',
        'order_date',
        'total_amount',
        'status',
        'remarks',
        'invoice_path',
        'journal_entry_id',
        'payment_journal_entry_id',
    ];

    protected $casts = [
        'order_date'   => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function paymentJournalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'payment_journal_entry_id');
    }
}