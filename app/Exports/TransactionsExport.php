<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    use Exportable;

    public function __construct(private array $filters = []) {}

    public function query()
    {
        $query = Transaction::with('user')->latest();

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (!empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }
        if (!empty($this->filters['is_flagged'])) {
            $query->where('is_flagged', true);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Transaction ID', 'User', 'Category', 'Type', 'Amount', 'Currency',
            'Fee', 'Net Amount', 'Status', 'Payment Method',
            'Sender Name', 'Sender Account', 'Sender Bank',
            'Receiver Name', 'Receiver Account', 'Receiver Bank',
            'Risk Score', 'Flagged', 'Reference', 'Country', 'IP Address',
            'Processed At', 'Created At',
        ];
    }

    public function map($t): array
    {
        return [
            $t->transaction_id,
            $t->user?->name ?? 'N/A',
            $t->category,
            strtoupper($t->type),
            $t->amount,
            $t->currency,
            $t->fee,
            $t->net_amount,
            strtoupper($t->status),
            $t->payment_method,
            $t->sender_name,
            $t->sender_account,
            $t->sender_bank,
            $t->receiver_name,
            $t->receiver_account,
            $t->receiver_bank,
            $t->risk_score,
            $t->is_flagged ? 'Yes' : 'No',
            $t->reference,
            $t->country,
            $t->ip_address,
            $t->processed_at?->format('Y-m-d H:i:s'),
            $t->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ], 'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]],
        ];
    }
}
