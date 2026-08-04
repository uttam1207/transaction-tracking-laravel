<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ASDairy Stock Management Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #4f46e5; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #1e1b4b; font-size: 20px; }
        .header p { margin: 3px 0 0; color: #666; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f8fafc; color: #1e293b; font-weight: bold; }
        .text-right { text-align: right; }
        .badge { padding: 3px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-warning { background-color: #fef9c3; color: #854d0e; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ASDairy — Stock Management Report</h1>
        <p>Anoo Village, Damoh District, Madhya Pradesh | Generated on: {{ now()->format('d-M-Y H:i A') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Category</th>
                <th>Unit</th>
                <th class="text-right">Available Stock</th>
                <th class="text-right">Reorder Level</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td><strong>{{ $item->name }}</strong></td>
                    <td>{{ $item->category }}</td>
                    <td>{{ $item->unit }}</td>
                    <td class="text-right"><strong>{{ number_format($item->available_quantity, 2) }}</strong></td>
                    <td class="text-right">{{ number_format($item->min_stock, 2) }}</td>
                    <td>
                        @if($item->stock_status === 'Optimal')
                            <span class="badge badge-success">Optimal</span>
                        @elseif($item->stock_status === 'Low Stock')
                            <span class="badge badge-warning">Low Stock</span>
                        @else
                            <span class="badge badge-danger">Out of Stock</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
