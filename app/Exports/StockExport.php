<?php

namespace App\Exports;

use App\Models\InventoryItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(private array $filters = []) {}

    public function title(): string
    {
        return 'Current Stock';
    }

    public function collection()
    {
        $query = InventoryItem::with('stockMovements')->orderBy('category')->orderBy('name');

        if (!empty($this->filters['category'])) {
            $query->where('category', $this->filters['category']);
        }
        if (!empty($this->filters['status'])) {
            $items = $query->get();
            return $items->filter(fn ($i) =>
                strtolower(str_replace(' ', '_', $i->stock_status)) === strtolower($this->filters['status'])
            );
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            '#',
            'Item Name',
            'Category',
            'Item Type',
            'Unit',
            'Available Qty',
            'Min Stock',
            'Stock Status',
            'Expiry Date',
            'Active',
        ];
    }

    public function map($item): array
    {
        static $row = 0;
        $row++;

        return [
            $row,
            $item->name,
            $item->category,
            $item->item_type ?? '—',
            $item->unit,
            number_format($item->available_quantity, 2),
            number_format($item->min_stock, 2),
            $item->stock_status,
            $item->expiry_date ? $item->expiry_date->format('d M Y') : '—',
            $item->is_active ? 'Active' : 'Inactive',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                  'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF0D9488']]],
        ];
    }
}
