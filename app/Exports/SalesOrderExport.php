<?php

namespace App\Exports;

use App\Models\SalesOrder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesOrderExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function __construct(
        private ?string $dateFrom = null,
        private ?string $dateTo   = null,
        private ?string $status   = null,
        private ?string $itemType = null,
    ) {}

    public function collection(): Collection
    {
        $q = SalesOrder::with('customer', 'saleItemType')
            ->latest('sale_date');

        if ($this->dateFrom) $q->whereDate('sale_date', '>=', $this->dateFrom);
        if ($this->dateTo)   $q->whereDate('sale_date', '<=', $this->dateTo);
        if ($this->status)   $q->where('payment_status', $this->status);
        if ($this->itemType) $q->where('item_type', $this->itemType);

        return $q->get()->map(fn ($s) => [
            $s->invoice_number,
            $s->sale_date->format('d/m/Y'),
            $s->customer?->name ?? 'Retail Customer',
            $s->item_type,
            $s->quantity ?? '—',
            $s->fat_percentage ?? '—',
            $s->fat_rate ?? '—',
            number_format((float) $s->total_amount, 2),
            $s->payment_status,
            $s->journal_entry_id ? 'JE-'.$s->journal_entry_id : 'Not Posted',
        ]);
    }

    public function headings(): array
    {
        return [
            'Invoice #', 'Date', 'Customer', 'Item Type',
            'Qty (L)', 'Fat %', 'Fat Rate', 'Amount (₹)',
            'Payment Status', 'Journal Entry',
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 18, 'B' => 14, 'C' => 24, 'D' => 20,
                'E' => 10, 'F' => 10, 'G' => 12, 'H' => 16,
                'I' => 16, 'J' => 18];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '059669']], 'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }

    public function title(): string { return 'Sales Invoices'; }
}