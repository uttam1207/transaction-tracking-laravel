<?php

namespace App\Exports;

use App\Models\Expense;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpensesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    use Exportable;

    public function __construct(private array $filters = []) {}

    public function query()
    {
        $query = Expense::with('category', 'recorder')->latest('expense_date');

        if (!empty($this->filters['category_id'])) {
            $query->where('expense_category_id', $this->filters['category_id']);
        }
        if (!empty($this->filters['payment_status'])) {
            $query->where('payment_status', $this->filters['payment_status']);
        }
        if (!empty($this->filters['payment_method'])) {
            $query->where('payment_method', $this->filters['payment_method']);
        }
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('expense_date', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('expense_date', '<=', $this->filters['date_to']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            '#', 'Date', 'Category', 'Description', 'Amount (INR)',
            'Vendor / Payee', 'Payment Method', 'Payment Status',
            'Reference No.', 'Remarks', 'Recorded By', 'Created At',
        ];
    }

    public function map($e): array
    {
        return [
            $e->id,
            $e->expense_date->format('d-m-Y'),
            $e->category?->name ?? 'N/A',
            $e->description,
            number_format($e->amount, 2),
            $e->vendor_payee ?? '-',
            $e->payment_method_label,
            ucfirst($e->payment_status),
            $e->reference_number ?? '-',
            $e->remarks ?? '-',
            $e->recorder?->name ?? 'N/A',
            $e->created_at->format('d-m-Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
            ],
        ];
    }
}
