<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComplianceDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComplianceController extends Controller
{
    public function index(Request $request)
    {
        $query = ComplianceDocument::query();

        if ($request->search) {
            $query->where(fn($q) => $q
                ->where('document_title', 'like', '%'.$request->search.'%')
                ->orWhere('document_number', 'like', '%'.$request->search.'%'));
        }
        if ($request->category) {
            $query->where('category', $request->category);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $documents = $query->latest()->paginate(15)->withQueryString();
        $summary = [
            'active_docs' => ComplianceDocument::where('status', 'Active')->count(),
            'expiring_soon' => ComplianceDocument::whereDate('expiry_date', '<=', now()->addDays(30))->count(),
        ];
        return view('admin.compliance.index', compact('documents', 'summary'));
    }

    public function create()
    {
        return view('admin.compliance.create');
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
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('compliance-docs', 'uploads');
        }
        unset($validated['file']);

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
            'file'            => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($request->hasFile('file')) {
            if ($complianceDocument->file_path) {
                Storage::disk('uploads')->delete($complianceDocument->file_path);
            }
            $validated['file_path'] = $request->file('file')->store('compliance-docs', 'uploads');
        }
        unset($validated['file']);

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
