<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComplianceDocument;
use Illuminate\Http\Request;

class ComplianceController extends Controller
{
    public function index()
    {
        $documents = ComplianceDocument::latest()->paginate(15);
        $summary = [
            'active_docs' => ComplianceDocument::where('status', 'Active')->count(),
            'expiring_soon' => ComplianceDocument::whereDate('expiry_date', '<=', now()->addDays(30))->count(),
        ];
        return view('admin.compliance.index', compact('documents', 'summary'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_title' => 'required|string|max:150',
            'category' => 'required|in:FSSAI,Animal Insurance,Vaccination Certificates,Government Licenses,Bank Loan Documents,Land Records,Audit Files',
            'document_number' => 'nullable|string|max:100',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'status' => 'required|in:Active,Expiring Soon,Expired',
        ]);

        ComplianceDocument::create($validated);
        return redirect()->route('admin.compliance.index')->with('success', 'Compliance document recorded.');
    }

    public function show(ComplianceDocument $complianceDocument)
    {
        return view('admin.compliance.show', compact('complianceDocument'));
    }

    public function edit(ComplianceDocument $complianceDocument)
    {
        return view('admin.compliance.edit', compact('complianceDocument'));
    }

    public function update(Request $request, ComplianceDocument $complianceDocument)
    {
        $validated = $request->validate([
            'document_title'  => 'required|string|max:150',
            'category'        => 'required|in:FSSAI,Animal Insurance,Vaccination Certificates,Government Licenses,Bank Loan Documents,Land Records,Audit Files',
            'document_number' => 'nullable|string|max:100',
            'issue_date'      => 'nullable|date',
            'expiry_date'     => 'nullable|date',
            'status'          => 'required|in:Active,Expiring Soon,Expired',
        ]);

        $complianceDocument->update($validated);

        return redirect()->route('admin.compliance.show', $complianceDocument)
            ->with('success', 'Compliance document updated.');
    }

    public function destroy(ComplianceDocument $complianceDocument)
    {
        $complianceDocument->delete();
        return redirect()->route('admin.compliance.index')
            ->with('success', 'Compliance document deleted.');
    }
}
