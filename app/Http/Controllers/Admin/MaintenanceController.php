<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MachineMaintenance;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index()
    {
        $logs = MachineMaintenance::latest('service_date')->paginate(15);
        $summary = [
            'total_cost' => MachineMaintenance::sum('cost'),
            'due_soon' => MachineMaintenance::where('next_service_due', '<=', now()->addDays(14))->count(),
        ];
        return view('admin.maintenance.index', compact('logs', 'summary'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'machine_name' => 'required|string|max:100',
            'maintenance_type' => 'required|in:Preventive Schedule,Service History,Breakdown Log',
            'service_date' => 'required|date',
            'cost' => 'nullable|numeric|min:0',
            'serviced_by' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'next_service_due' => 'nullable|date',
        ]);

        MachineMaintenance::create($validated);
        return redirect()->route('admin.maintenance.index')->with('success', 'Maintenance record logged.');
    }
}
