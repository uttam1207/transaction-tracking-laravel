<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchaseOrderExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function __construct(
        private ?string $dateFrom = null,
        private ?string $dateTo   = null,
        private ?string $status   = null,
    ) {}

    public function collection(): Collection
    {
        $q = PurchaseOrder::with('vendor')->latest('order_date');

        if ($this->dateFrom) $q->whereDate('order_date', '>=', $this->dateFrom);
        if ($this->dateTo)   $q->whereDate('order_date', '<=', $this->dateTo);
        if ($this->status)   $q->where('status', $this->status);

        return $q->get()->map(fn ($p) => [
            $p->po_number,
            $p->order_date->format('d/m/Y'),
            $p->vendor?->name ?? '—',
            number_format((float) $p->total_amount, 2),
            $p->status,
            $p->remarks ?? '—',
            $p->journal_entry_id         ? 'JE-'.$p->journal_entry_id         : '—',
            $p->payment_journal_entry_id ? 'JE-'.$p->payment_journal_entry_id : '—',
        ]);
    }

    public function headings(): array
    {
        return [
            'PO #', 'Order Date', 'Vendor', 'Amount (₹)',
            'Status', 'Remarks', 'Goods Receipt JE', 'Payment JE',
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 18, 'B' => 14, 'C' => 26, 'D' => 16,
                'E' => 14, 'F' => 28, 'G' => 18, 'H' => 18];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2563EB']]],
        ];
    }

    public function title(): string { return 'Purchase Orders'; }
}