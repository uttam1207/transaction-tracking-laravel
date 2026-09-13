<?php

namespace App\Exports;

use App\Models\JournalEntry;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JournalEntryExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function __construct(
        private ?string $dateFrom = null,
        private ?string $dateTo   = null,
        private ?string $type     = null,
        private ?string $status   = null,
        private ?int    $periodId = null,
    ) {}

    public function collection(): Collection
    {
        $q = JournalEntry::with('period', 'createdBy')->latest('entry_date');

        if ($this->dateFrom) $q->whereDate('entry_date', '>=', $this->dateFrom);
        if ($this->dateTo)   $q->whereDate('entry_date', '<=', $this->dateTo);
        if ($this->type)     $q->where('type', $this->type);
        if ($this->status)   $q->where('status', $this->status);
        if ($this->periodId) $q->where('period_id', $this->periodId);

        return $q->get()->map(fn ($je) => [
            $je->entry_number,
            $je->entry_date->format('d/m/Y'),
            ucfirst($je->type),
            $je->reference ?? '—',
            $je->description,
            number_format((float) $je->total_debit, 2),
            number_format((float) $je->total_credit, 2),
            ucfirst($je->status),
            $je->period?->name ?? '—',
            $je->createdBy?->name ?? '—',
        ]);
    }

    public function headings(): array
    {
        return [
            'Entry #', 'Date', 'Type', 'Reference', 'Description',
            'Total Debit (₹)', 'Total Credit (₹)', 'Status', 'Period', 'Created By',
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 18, 'B' => 14, 'C' => 14, 'D' => 20, 'E' => 40,
                'F' => 18, 'G' => 18, 'H' => 12, 'I' => 16, 'J' => 20];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '7C3AED']]],
        ];
    }

    public function title(): string { return 'Journal Entries'; }
}