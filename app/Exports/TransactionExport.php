<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function __construct(
        private ?string $dateFrom = null,
        private ?string $dateTo   = null,
        private ?string $type     = null,
        private ?string $status   = null,
        private ?string $category = null,
    ) {}

    public function collection(): Collection
    {
        $q = Transaction::latest('created_at');

        if ($this->dateFrom) $q->whereDate('created_at', '>=', $this->dateFrom);
        if ($this->dateTo)   $q->whereDate('created_at', '<=', $this->dateTo);
        if ($this->type)     $q->where('type', $this->type);
        if ($this->status)   $q->where('status', $this->status);
        if ($this->category) $q->where('category', $this->category);

        return $q->get()->map(fn ($t) => [
            $t->transaction_id,
            $t->created_at->format('d/m/Y H:i'),
            ucfirst($t->type),
            ucfirst($t->category ?? '—'),
            number_format((float) $t->amount, 2),
            number_format((float) $t->fee, 2),
            number_format((float) $t->net_amount, 2),
            ucfirst($t->status),
            $t->payment_method ?? '—',
            $t->journal_entry_id ? 'JE-'.$t->journal_entry_id : '—',
        ]);
    }

    public function headings(): array
    {
        return [
            'Transaction ID', 'Date & Time', 'Type', 'Category',
            'Amount (₹)', 'Fee (₹)', 'Net Amount (₹)', 'Status',
            'Payment Method', 'Journal Entry',
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 28, 'B' => 18, 'C' => 12, 'D' => 16,
                'E' => 14, 'F' => 12, 'G' => 16, 'H' => 12,
                'I' => 18, 'J' => 16];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']]],
        ];
    }

    public function title(): string { return 'Transactions'; }
}