<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Breed;
use App\Models\Contact;
use App\Models\Department;
use App\Models\Document;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\FraudAlert;
use App\Models\InventoryItem;
use App\Models\MilkEntry;
use App\Models\Project;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2|max:100']);
        $q = $request->q;

        $results = [];

        // ── Animals ───────────────────────────────────────────────────────────
        Animal::where('tag_number', 'like', "%{$q}%")
            ->orWhere('name',       'like', "%{$q}%")
            ->orWhere('animal_id',  'like', "%{$q}%")
            ->orWhere('shed_number','like', "%{$q}%")
            ->orWhere('breed',      'like', "%{$q}%")
            ->limit(5)->get()
            ->each(function ($a) use (&$results) {
                $results[] = [
                    'type'     => 'Animal',
                    'icon'     => 'tags',
                    'color'    => 'success',
                    'title'    => $a->tag_number . ($a->name ? ' — ' . $a->name : ''),
                    'subtitle' => ($a->animal_type ?? 'Animal') . ' · ' . ($a->shed_number ?? 'No Shed') . ' · ' . ($a->status ?? ''),
                    'url'      => route('admin.animals.show', $a->id),
                ];
            });

        // ── Transactions ──────────────────────────────────────────────────────
        Transaction::where('transaction_id', 'like', "%{$q}%")
            ->orWhere('sender_name',   'like', "%{$q}%")
            ->orWhere('receiver_name', 'like', "%{$q}%")
            ->orWhere('reference',     'like', "%{$q}%")
            ->limit(4)->get()
            ->each(function ($t) use (&$results) {
                $results[] = [
                    'type'     => 'Transaction',
                    'icon'     => 'arrow-left-right',
                    'color'    => 'primary',
                    'title'    => $t->transaction_id,
                    'subtitle' => $t->sender_name . ' → ' . $t->receiver_name . ' · ₹' . number_format($t->amount, 2),
                    'url'      => route('admin.transactions.show', $t->id),
                ];
            });

        // ── Expenses ──────────────────────────────────────────────────────────
        Expense::where('description',    'like', "%{$q}%")
            ->orWhere('vendor_payee',     'like', "%{$q}%")
            ->orWhere('reference_number', 'like', "%{$q}%")
            ->orWhere('remarks',          'like', "%{$q}%")
            ->limit(4)->get()
            ->each(function ($e) use (&$results) {
                $results[] = [
                    'type'     => 'Expense',
                    'icon'     => 'receipt',
                    'color'    => 'warning',
                    'title'    => $e->vendor_payee ?? ('Expense #' . $e->id),
                    'subtitle' => '₹' . number_format($e->amount, 2) . ' · ' . ($e->description ? Str::limit($e->description, 40) : ''),
                    'url'      => route('admin.expenses.show', $e->id),
                ];
            });

        // ── Users ─────────────────────────────────────────────────────────────
        User::where('name',  'like', "%{$q}%")
            ->orWhere('email','like', "%{$q}%")
            ->limit(4)->get()
            ->each(function ($u) use (&$results) {
                $results[] = [
                    'type'     => 'User',
                    'icon'     => 'person',
                    'color'    => 'primary',
                    'title'    => $u->name,
                    'subtitle' => $u->email . ' · ' . ucfirst(str_replace('_', ' ', $u->role ?? '')),
                    'url'      => route('admin.users.show', $u->id),
                ];
            });

        // ── Employees ─────────────────────────────────────────────────────────
        Employee::with('user')
            ->where('employee_id',  'like', "%{$q}%")
            ->orWhere('designation','like', "%{$q}%")
            ->orWhereHas('user', fn($query) => $query->where('name',  'like', "%{$q}%")
                                                      ->orWhere('email','like', "%{$q}%"))
            ->limit(4)->get()
            ->each(function ($emp) use (&$results) {
                $results[] = [
                    'type'     => 'Employee',
                    'icon'     => 'person-badge',
                    'color'    => 'info',
                    'title'    => $emp->full_name ?? ($emp->user?->name ?? 'Employee'),
                    'subtitle' => $emp->employee_id . ' · ' . ($emp->designation ?? ''),
                    'url'      => route('admin.employees.show', $emp->id),
                ];
            });

        // ── Contacts ──────────────────────────────────────────────────────────
        Contact::where('name',    'like', "%{$q}%")
            ->orWhere('phone',    'like', "%{$q}%")
            ->orWhere('email',    'like', "%{$q}%")
            ->orWhere('company',  'like', "%{$q}%")
            ->orWhere('city',     'like', "%{$q}%")
            ->limit(4)->get()
            ->each(function ($c) use (&$results) {
                $results[] = [
                    'type'     => 'Contact',
                    'icon'     => 'person-lines-fill',
                    'color'    => 'success',
                    'title'    => $c->name,
                    'subtitle' => implode(' · ', array_filter([$c->company, $c->phone, $c->city])),
                    'url'      => route('admin.contacts.show', $c->id),
                ];
            });

        // ── Vendors ───────────────────────────────────────────────────────────
        Vendor::where('name',           'like', "%{$q}%")
            ->orWhere('contact_person',  'like', "%{$q}%")
            ->orWhere('phone',           'like', "%{$q}%")
            ->orWhere('email',           'like', "%{$q}%")
            ->orWhere('category',        'like', "%{$q}%")
            ->limit(3)->get()
            ->each(function ($v) use (&$results) {
                $results[] = [
                    'type'     => 'Vendor',
                    'icon'     => 'shop',
                    'color'    => 'warning',
                    'title'    => $v->name,
                    'subtitle' => implode(' · ', array_filter([$v->category, $v->contact_person, $v->phone])),
                    'url'      => route('admin.vendors.index'),
                ];
            });

        // ── Stock / Inventory ─────────────────────────────────────────────────
        InventoryItem::where('name',    'like', "%{$q}%")
            ->orWhere('category',       'like', "%{$q}%")
            ->orWhere('description',    'like', "%{$q}%")
            ->limit(3)->get()
            ->each(function ($item) use (&$results) {
                $results[] = [
                    'type'     => 'Stock Item',
                    'icon'     => 'boxes',
                    'color'    => 'info',
                    'title'    => $item->name,
                    'subtitle' => ($item->category ?? '') . ' · ' . ($item->unit ?? '') . ($item->available_quantity !== null ? ' · ' . $item->available_quantity . ' in stock' : ''),
                    'url'      => route('admin.stock.index'),
                ];
            });

        // ── Projects ──────────────────────────────────────────────────────────
        Project::where('name',       'like', "%{$q}%")
            ->orWhere('code',        'like', "%{$q}%")
            ->orWhere('description', 'like', "%{$q}%")
            ->limit(3)->get()
            ->each(function ($p) use (&$results) {
                $results[] = [
                    'type'     => 'Project',
                    'icon'     => 'kanban',
                    'color'    => 'primary',
                    'title'    => $p->name,
                    'subtitle' => ($p->code ?? '') . ' · Status: ' . ($p->status ?? ''),
                    'url'      => Route::has('admin.projects.show') ? route('admin.projects.show', $p->id) : route('admin.projects.index'),
                ];
            });

        // ── Departments ───────────────────────────────────────────────────────
        Department::where('name',       'like', "%{$q}%")
            ->orWhere('code',           'like', "%{$q}%")
            ->orWhere('description',    'like', "%{$q}%")
            ->limit(3)->get()
            ->each(function ($d) use (&$results) {
                $results[] = [
                    'type'     => 'Department',
                    'icon'     => 'building',
                    'color'    => 'secondary',
                    'title'    => $d->name,
                    'subtitle' => ($d->code ?? '') . ($d->description ? ' · ' . Str::limit($d->description, 40) : ''),
                    'url'      => route('admin.departments.index'),
                ];
            });

        // ── Tasks ─────────────────────────────────────────────────────────────
        Task::where('title',       'like', "%{$q}%")
            ->orWhere('description','like', "%{$q}%")
            ->limit(3)->get()
            ->each(function ($task) use (&$results) {
                $results[] = [
                    'type'     => 'Task',
                    'icon'     => 'check2-square',
                    'color'    => 'warning',
                    'title'    => $task->title,
                    'subtitle' => 'Priority: ' . ($task->priority ?? '—') . ' · Status: ' . ($task->status ?? '—'),
                    'url'      => route('admin.tasks.index'),
                ];
            });

        // ── Breeds ────────────────────────────────────────────────────────────
        Breed::where('name',       'like', "%{$q}%")
            ->orWhere('animal_type','like', "%{$q}%")
            ->limit(3)->get()
            ->each(function ($b) use (&$results) {
                $results[] = [
                    'type'     => 'Breed',
                    'icon'     => 'award',
                    'color'    => 'success',
                    'title'    => $b->name,
                    'subtitle' => 'Type: ' . ($b->animal_type ?? '—'),
                    'url'      => route('admin.breeds.index'),
                ];
            });

        // ── Fraud Alerts ──────────────────────────────────────────────────────
        FraudAlert::where('description','like', "%{$q}%")
            ->orWhere('alert_type',      'like', "%{$q}%")
            ->limit(3)->get()
            ->each(function ($alert) use (&$results) {
                $results[] = [
                    'type'     => 'Fraud Alert',
                    'icon'     => 'shield-exclamation',
                    'color'    => 'danger',
                    'title'    => ucfirst(str_replace('_', ' ', $alert->alert_type)),
                    'subtitle' => 'Severity: ' . $alert->severity . ' · ' . $alert->status,
                    'url'      => route('admin.fraud-alerts.show', $alert->id),
                ];
            });

        // ── Documents ─────────────────────────────────────────────────────────
        Document::where('title',       'like', "%{$q}%")
            ->orWhere('description',   'like', "%{$q}%")
            ->orWhere('category',      'like', "%{$q}%")
            ->orWhere('file_name',     'like', "%{$q}%")
            ->limit(4)->get()
            ->each(function ($doc) use (&$results) {
                $results[] = [
                    'type'     => 'Document',
                    'icon'     => 'file-earmark-text',
                    'color'    => 'primary',
                    'title'    => $doc->title,
                    'subtitle' => ucfirst($doc->category ?? 'General') . ($doc->file_name ? ' · ' . $doc->file_name : ''),
                    'url'      => route('documents.preview', $doc->id),
                ];
            });

        // ── Milk Entries ──────────────────────────────────────────────────────
        MilkEntry::where('animal_id',    'like', "%{$q}%")
            ->orWhere('shed_number',     'like', "%{$q}%")
            ->orWhere('quality_rating',  'like', "%{$q}%")
            ->orWhere('shift',           'like', "%{$q}%")
            ->limit(3)->get()
            ->each(function ($m) use (&$results) {
                $results[] = [
                    'type'     => 'Milk Entry',
                    'icon'     => 'droplet-fill',
                    'color'    => 'success',
                    'title'    => ($m->date ? $m->date->format('d M Y') : '#' . $m->id) . ' — ' . $m->shift . ' Shift',
                    'subtitle' => number_format($m->quantity_liters, 1) . ' L · ' . ($m->quality_rating ?? '') . ($m->shed_number ? ' · Shed ' . $m->shed_number : ''),
                    'url'      => route('admin.milk.show', $m->id),
                ];
            });

        return response()->json([
            'success' => true,
            'query'   => $q,
            'count'   => count($results),
            'results' => $results,
        ]);
    }
}
