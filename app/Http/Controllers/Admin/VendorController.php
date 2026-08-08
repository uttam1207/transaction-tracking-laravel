<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::withCount('purchaseOrders')
            ->orderBy('name')
            ->get();

        $categories = ['Feed Supplier', 'Medicine Supplier', 'Equipment Supplier', 'Fodder Supplier', 'Veterinary', 'Other'];

        return view('admin.vendors.index', compact('vendors', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:150|unique:vendors,name',
            'contact_person' => 'nullable|string|max:100',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:100',
            'category'       => 'nullable|string|max:80',
            'address'        => 'nullable|string|max:255',
        ]);

        Vendor::create($validated);

        return back()->with('success', 'Vendor "' . $validated['name'] . '" added.');
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:150|unique:vendors,name,' . $vendor->id,
            'contact_person' => 'nullable|string|max:100',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:100',
            'category'       => 'nullable|string|max:80',
            'address'        => 'nullable|string|max:255',
        ]);

        $vendor->update($validated);

        return back()->with('success', 'Vendor updated.');
    }

    public function destroy(Vendor $vendor)
    {
        if ($vendor->purchaseOrders()->exists()) {
            return back()->with('error', 'Cannot delete — vendor has existing purchase orders.');
        }
        $vendor->delete();
        return back()->with('success', 'Vendor deleted.');
    }
}
