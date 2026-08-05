<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MachineMaintenance;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = MachineMaintenance::query();

        if ($request->search) {
            $query->where('machine_name', 'like', '%'.$request->search.'%');
        }
        if ($request->maintenance_type) {
            $query->where('maintenance_type', $request->maintenance_type);
        }

        $logs = $query->latest('service_date')->paginate(15)->withQueryString();
        $summary = [
            'total_cost' => MachineMaintenance::sum('cost'),
            'due_soon' => MachineMaintenance::where('next_service_due', '<=', now()->addDays(14))->count(),
        ];
        return view('admin.maintenance.index', compact('logs', 'summary'));
    }

    public function create()
    {
        return view('admin.maintenance.create');
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

    public function show(MachineMaintenance $machineMaintenance)
    {
        return view('admin.maintenance.show', compact('machineMaintenance'));
    }

    public function edit(MachineMaintenance $machineMaintenance)
    {
        return view('admin.maintenance.edit', compact('machineMaintenance'));
    }

    public function update(Request $request, MachineMaintenance $machineMaintenance)
    {
        $validated = $request->validate([
            'machine_name'     => 'required|string|max:100',
            'maintenance_type' => 'required|in:Preventive Schedule,Service History,Breakdown Log',
            'service_date'     => 'required|date',
            'cost'             => 'nullable|numeric|min:0',
            'serviced_by'      => 'nullable|string|max:100',
            'description'      => 'nullable|string|max:500',
            'next_service_due' => 'nullable|date',
        ]);

        $machineMaintenance->update($validated);

        return redirect()->route('admin.maintenance.show', $machineMaintenance)
            ->with('success', 'Maintenance record updated.');
    }

    public function destroy(MachineMaintenance $machineMaintenance)
    {
        $machineMaintenance->delete();
        return redirect()->route('admin.maintenance.index')
            ->with('success', 'Maintenance record deleted.');
    }
}
