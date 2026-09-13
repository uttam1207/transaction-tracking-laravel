<?php

namespace App\Exports;

use App\Models\Expense;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpenseExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function __construct(
        private ?string $dateFrom = null,
        private ?string $dateTo   = null,
        private ?string $status   = null,
        private ?int    $categoryId = null,
    ) {}

    public function collection(): Collection
    {
        $q = Expense::with('category', 'approvedBy')->latest('expense_date');

        if ($this->dateFrom)   $q->whereDate('expense_date', '>=', $this->dateFrom);
        if ($this->dateTo)     $q->whereDate('expense_date', '<=', $this->dateTo);
        if ($this->status)     $q->where('payment_status', $this->status);
        if ($this->categoryId) $q->where('expense_category_id', $this->categoryId);

        return $q->get()->map(fn ($e) => [
            $e->expense_date->format('d/m/Y'),
            $e->category?->name ?? '—',
            $e->description ?? '—',
            number_format((float) $e->amount, 2),
            ucfirst($e->payment_status ?? '—'),
            $e->payment_method ?? '—',
            $e->vendor_name ?? '—',
            $e->approvedBy?->name ?? '—',
        ]);
    }

    public function headings(): array
    {
        return [
            'Date', 'Category', 'Description', 'Amount (₹)',
            'Payment Status', 'Payment Method', 'Vendor', 'Approved By',
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 14, 'B' => 22, 'C' => 36, 'D' => 14,
                'E' => 16, 'F' => 18, 'G' => 24, 'H' => 20];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DC2626']]],
        ];
    }

    public function title(): string { return 'Expenses'; }
}