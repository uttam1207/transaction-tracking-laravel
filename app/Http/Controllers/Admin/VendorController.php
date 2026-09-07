<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorCategory;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::withCount('purchaseOrders')
            ->orderBy('name')
            ->get();

        $categories    = VendorCategory::orderBy('name')->pluck('name')->toArray();
        $allCategories = VendorCategory::orderBy('name')->get();

        // Count vendors per category (a vendor may appear in multiple buckets)
        $categoryStats = [];
        foreach ($categories as $cat) {
            $categoryStats[$cat] = $vendors->filter(
                fn($v) => is_array($v->category) && in_array($cat, $v->category)
            )->count();
        }

        return view('admin.vendors.index', compact('vendors', 'categories', 'categoryStats', 'allCategories'));
    }

    public function store(Request $request)
    {
        $categoryNames = VendorCategory::pluck('name')->toArray();

        $request->validate([
            'name'           => 'required|string|max:150|unique:vendors,name',
            'contact_person' => 'nullable|string|max:100',
            'phone'          => 'required|regex:/^[6-9][0-9]{9}$/',
            'email'          => 'required|email|max:100',
            'category'       => 'nullable|array',
            'category.*'     => 'string|in:' . implode(',', $categoryNames),
            'address'        => 'required|string|max:255',
        ]);

        Vendor::create([
            'name'           => $request->name,
            'contact_person' => $request->contact_person,
            'phone'          => $request->phone,
            'email'          => $request->email,
            'category'       => $request->input('category', []),
            'address'        => $request->address,
        ]);

        return back()->with('success', 'Vendor "' . $request->name . '" added.');
    }

    public function update(Request $request, Vendor $vendor)
    {
        $categoryNames = VendorCategory::pluck('name')->toArray();

        $request->validate([
            'name'           => 'required|string|max:150|unique:vendors,name,' . $vendor->id,
            'contact_person' => 'nullable|string|max:100',
            'phone'          => 'required|regex:/^[6-9][0-9]{9}$/',
            'email'          => 'required|email|max:100',
            'category'       => 'nullable|array',
            'category.*'     => 'string|in:' . implode(',', $categoryNames),
            'address'        => 'required|string|max:255',
        ]);

        $vendor->update([
            'name'           => $request->name,
            'contact_person' => $request->contact_person,
            'phone'          => $request->phone,
            'email'          => $request->email,
            'category'       => $request->input('category', []),
            'address'        => $request->address,
        ]);

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

    // ── Category Management (AJAX) ────────────────────────────────────────

    public function categoriesIndex()
    {
        return response()->json(VendorCategory::orderBy('name')->get());
    }

    public function categoriesStore(Request $request)
    {
        $request->validate(['name' => 'required|string|max:80|unique:vendor_categories,name']);
        $cat = VendorCategory::create(['name' => $request->name]);
        return response()->json(['success' => true, 'category' => $cat]);
    }

    public function categoriesDestroy(VendorCategory $vendorCategory)
    {
        $vendorCategory->delete();
        return response()->json(['success' => true]);
    }
}
