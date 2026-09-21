<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\CrmCustomer;
use App\Models\JournalEntry;
use App\Models\SaleItemType;
use App\Models\SaleOrderItem;
use App\Models\Transaction;

class SalesOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'crm_customer_id',
        'item_type',
        'sale_item_type_id',
        'sale_date',
        'due_date',
        'payment_terms',
        'quantity',
        'rate',
        'fat_percentage',
        'fat_rate',
        'total_amount',
        'amount_paid',
        'payment_status',
        'journal_entry_id',
        'transaction_id',
    ];

    protected $casts = [
        'sale_date'      => 'date',
        'due_date'       => 'date',
        'quantity'       => 'decimal:2',
        'rate'           => 'decimal:2',
        'fat_percentage' => 'decimal:2',
        'fat_rate'       => 'decimal:2',
        'total_amount'   => 'decimal:2',
        'amount_paid'    => 'decimal:2',
    ];

    /** Amount still owed on this invoice. */
    public function getOutstandingAttribute(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->amount_paid);
    }

    /**
     * Generate a sequential invoice number in Indian fiscal-year-aware format:
     *   ASD/YY-YY/NNNNNN  (e.g. ASD/26-27/000001)
     *
     * Indian FY: April–March.  Dates in Jan–Mar belong to the FY that started
     * the previous April (e.g. Jan 2027 → FY 26-27).
     *
     * Uses a DB-level lock inside a transaction to prevent duplicates under
     * concurrent requests.  Existing historical invoice numbers (INV-YYYY-*)
     * are not touched.
     */
    public static function generateNumber(): string
    {
        $month = now()->month;
        $year  = now()->year;

        // April–December: FY started this calendar year
        // January–March:  FY started the previous calendar year
        $fyStart = $month >= 4 ? $year : $year - 1;
        $fyEnd   = $fyStart + 1;

        $prefix = 'ASD/' . substr((string) $fyStart, 2, 2) . '-' . substr((string) $fyEnd, 2, 2) . '/';

        // Lock the latest row for this FY prefix to get a safe sequential number
        $last = static::withTrashed()
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->lockForUpdate()
            ->value('invoice_number');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function customer()
    {
        return $this->belongsTo(CrmCustomer::class, 'crm_customer_id');
    }

    public function saleItemType()
    {
        return $this->belongsTo(SaleItemType::class, 'sale_item_type_id');
    }

    public function items()
    {
        return $this->hasMany(SaleOrderItem::class)->orderBy('sort_order');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}