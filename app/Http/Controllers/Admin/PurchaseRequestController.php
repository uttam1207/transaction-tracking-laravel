<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\InventoryItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseRequest::with('requestedBy', 'department', 'approvedBy');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->priority) {
            $query->where('priority', $request->priority);
        }
        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('pr_number', 'like', "%{$request->search}%")
                  ->orWhere('purpose', 'like', "%{$request->search}%");
            });
        }
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $requests    = $query->latest()->paginate(20)->withQueryString();
        $departments = Department::orderBy('name')->get();

        $stats = [
            'draft'     => PurchaseRequest::where('status', 'draft')->count(),
            'submitted' => PurchaseRequest::where('status', 'submitted')->count(),
            'approved'  => PurchaseRequest::where('status', 'approved')->count(),
            'rejected'  => PurchaseRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.purchase-requests.index', compact('requests', 'departments', 'stats'));
    }

    public function create()
    {
        $departments     = Department::orderBy('name')->get();
        $inventoryItems  = InventoryItem::where('is_active', true)->orderBy('name')->get();
        return view('admin.purchase-requests.create', compact('departments', 'inventoryItems'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'required_date'         => 'nullable|date',
            'department_id'         => 'nullable|exists:departments,id',
            'purpose'               => 'nullable|string',
            'priority'              => 'required|in:low,normal,high,urgent',
            'items'                 => 'required|array|min:1',
            'items.*.item_name'     => 'required|string|max:200',
            'items.*.quantity'      => 'required|numeric|min:0.01',
            'items.*.unit'          => 'nullable|string|max:30',
        ]);

        DB::transaction(function () use ($request) {
            $pr = PurchaseRequest::create([
                'pr_number'     => PurchaseRequest::generateNumber(),
                'requested_by'  => auth()->id(),
                'department_id' => $request->department_id,
                'required_date' => $request->required_date,
                'purpose'       => $request->purpose,
                'priority'      => $request->priority,
                'status'        => 'draft',
            ]);

            foreach ($request->items as $item) {
                $qty        = $item['quantity'];
                $unitPrice  = $item['estimated_unit_price'] ?? null;
                PurchaseRequestItem::create([
                    'purchase_request_id'  => $pr->id,
                    'item_name'            => $item['item_name'],
                    'description'          => $item['description'] ?? null,
                    'quantity'             => $qty,
                    'unit'                 => $item['unit'] ?? 'pcs',
                    'estimated_unit_price' => $unitPrice,
                    'estimated_total'      => $unitPrice ? $qty * $unitPrice : null,
                    'inventory_item_id'    => $item['inventory_item_id'] ?? null,
                ]);
            }
        });

        return redirect()->route('admin.purchase-requests.index')->with('success', 'Purchase request created.');
    }

    public function show(PurchaseRequest $pr)
    {
        $pr->load('requestedBy', 'department', 'approvedBy', 'items.inventoryItem');
        return view('admin.purchase-requests.show', compact('pr'));
    }

    public function submit(PurchaseRequest $pr)
    {
        if ($pr->status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Only draft PRs can be submitted.'], 422);
        }
        $pr->update(['status' => 'submitted']);
        return response()->json(['success' => true, 'message' => 'Purchase request submitted for approval.']);
    }

    public function approve(Request $request, PurchaseRequest $pr)
    {
        if ($pr->status !== 'submitted') {
            return response()->json(['success' => false, 'message' => 'Only submitted PRs can be approved.'], 422);
        }
        $pr->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        return response()->json(['success' => true, 'message' => 'Purchase request approved.']);
    }

    public function reject(Request $request, PurchaseRequest $pr)
    {
        $request->validate(['rejection_reason' => 'required|string']);

        if (!in_array($pr->status, ['submitted', 'approved'])) {
            return response()->json(['success' => false, 'message' => 'Cannot reject this PR.'], 422);
        }
        $pr->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by'      => auth()->id(),
            'approved_at'      => now(),
        ]);
        return response()->json(['success' => true, 'message' => 'Purchase request rejected.']);
    }
}
