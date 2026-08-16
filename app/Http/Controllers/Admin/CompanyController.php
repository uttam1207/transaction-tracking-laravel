<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function index()
    {
        $company = Company::with('activeBranches')->first();
        return view('admin.company.index', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('admin.company.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:255',
            'code'                 => 'required|string|max:50|unique:companies,code,' . $company->id,
            'tagline'              => 'nullable|string|max:255',
            'address'              => 'nullable|string|max:500',
            'city'                 => 'nullable|string|max:100',
            'state'                => 'nullable|string|max:100',
            'country'              => 'nullable|string|max:100',
            'postal_code'          => 'nullable|string|max:20',
            'phone'                => 'nullable|string|max:30',
            'email'                => 'nullable|email|max:255',
            'website'              => 'nullable|url|max:255',
            'gst_number'           => 'nullable|string|max:50',
            'pan_number'           => 'nullable|string|max:20',
            'cin_number'           => 'nullable|string|max:50',
            'currency'             => 'nullable|string|max:10',
            'timezone'             => 'nullable|string|max:100',
            'financial_year_start' => 'nullable|integer|between:1,12',
            'description'          => 'nullable|string',
            'logo'                 => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $data['logo'] = $request->file('logo')->store('company', 'public');
        }

        $company->update($data);

        return redirect()->route('admin.company.index')
                         ->with('success', 'Company profile updated successfully.');
    }
}
