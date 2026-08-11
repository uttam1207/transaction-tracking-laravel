<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactCategory;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::with('category');

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('company', 'like', "%{$s}%")
                  ->orWhere('city', 'like', "%{$s}%");
            });
        }

        if ($request->category_id) {
            $query->where('contact_category_id', $request->category_id);
        }

        if ($request->status !== null && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        $contacts   = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = ContactCategory::orderBy('name')->get();

        $totalContacts  = Contact::count();
        $activeContacts = Contact::where('is_active', true)->count();
        $totalCategories = ContactCategory::count();
        $recentContacts = Contact::where('created_at', '>=', now()->subDays(30))->count();

        return view('admin.contacts.index', compact(
            'contacts', 'categories',
            'totalContacts', 'activeContacts', 'totalCategories', 'recentContacts'
        ));
    }

    public function create()
    {
        $categories = ContactCategory::where('is_active', true)->orderBy('name')->get();
        return view('admin.contacts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contact_category_id' => 'required|exists:contact_categories,id',
            'name'                => 'required|string|max:150',
            'phone'               => 'nullable|string|max:20',
            'alternate_phone'     => 'nullable|string|max:20',
            'email'               => 'nullable|email|max:150',
            'company'             => 'nullable|string|max:150',
            'designation'         => 'nullable|string|max:100',
            'address'             => 'nullable|string',
            'city'                => 'nullable|string|max:100',
            'state'               => 'nullable|string|max:100',
            'pincode'             => 'nullable|string|max:10',
            'notes'               => 'nullable|string',
            'is_active'           => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Contact::create($validated);

        return redirect()->route('admin.contacts.index')
            ->with('success', 'Contact "' . $validated['name'] . '" added successfully.');
    }

    public function show(Contact $contact)
    {
        $contact->load('category');
        return view('admin.contacts.show', compact('contact'));
    }

    public function edit(Contact $contact)
    {
        $categories = ContactCategory::where('is_active', true)->orderBy('name')->get();
        return view('admin.contacts.edit', compact('contact', 'categories'));
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'contact_category_id' => 'required|exists:contact_categories,id',
            'name'                => 'required|string|max:150',
            'phone'               => 'nullable|string|max:20',
            'alternate_phone'     => 'nullable|string|max:20',
            'email'               => 'nullable|email|max:150',
            'company'             => 'nullable|string|max:150',
            'designation'         => 'nullable|string|max:100',
            'address'             => 'nullable|string',
            'city'                => 'nullable|string|max:100',
            'state'               => 'nullable|string|max:100',
            'pincode'             => 'nullable|string|max:10',
            'notes'               => 'nullable|string',
            'is_active'           => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $contact->update($validated);

        return redirect()->route('admin.contacts.index')
            ->with('success', 'Contact updated successfully.');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return redirect()->route('admin.contacts.index')
            ->with('success', 'Contact deleted successfully.');
    }
}