<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmCustomer;
use App\Models\CrmLead;
use App\Models\CrmLeadActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CrmLeadController extends Controller
{
    public function index(Request $request)
    {
        $query = CrmLead::with('assignedTo', 'createdBy');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->priority) {
            $query->where('priority', $request->priority);
        }
        if ($request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('company', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $leads = $query->latest()->paginate(20)->withQueryString();
        $users = User::active()->orderBy('name')->get();

        $stats = [
            'total'    => CrmLead::count(),
            'new'      => CrmLead::where('status', 'new')->count(),
            'won'      => CrmLead::where('status', 'won')->count(),
            'lost'     => CrmLead::where('status', 'lost')->count(),
            'pipeline' => CrmLead::whereNotIn('status', ['won','lost'])->sum('deal_value'),
        ];

        $statuses  = ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost'];
        $priorities = ['low', 'medium', 'high'];

        return view('admin.crm-leads.index', compact('leads', 'users', 'stats', 'statuses', 'priorities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:200',
            'email'    => 'nullable|email|max:150',
            'priority' => 'required|in:low,medium,high',
            'status'   => 'required|in:new,contacted,qualified,proposal,negotiation,won,lost',
        ]);

        $lead = CrmLead::create([
            ...$request->only('name', 'company', 'email', 'phone', 'source', 'status',
                              'priority', 'deal_value', 'expected_close', 'notes', 'assigned_to'),
            'created_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Lead created.', 'lead' => $lead]);
    }

    public function show(CrmLead $lead)
    {
        $lead->load('assignedTo', 'createdBy', 'activities.createdBy', 'convertedCustomer');
        $users = User::active()->orderBy('name')->get();
        $activityTypes = ['call', 'email', 'meeting', 'note', 'demo', 'proposal'];
        return view('admin.crm-leads.show', compact('lead', 'users', 'activityTypes'));
    }

    public function update(Request $request, CrmLead $lead)
    {
        $request->validate([
            'name'     => 'required|string|max:200',
            'status'   => 'required|in:new,contacted,qualified,proposal,negotiation,won,lost',
            'priority' => 'required|in:low,medium,high',
        ]);

        $lead->update($request->only(
            'name', 'company', 'email', 'phone', 'source', 'status',
            'priority', 'deal_value', 'expected_close', 'notes', 'assigned_to'
        ));

        return response()->json(['success' => true, 'message' => 'Lead updated.']);
    }

    public function destroy(CrmLead $lead)
    {
        $lead->delete();
        return response()->json(['success' => true, 'message' => 'Lead deleted.']);
    }

    public function addActivity(Request $request, CrmLead $lead)
    {
        $request->validate([
            'type'        => 'required|in:call,email,meeting,note,demo,proposal',
            'subject'     => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'activity_at' => 'nullable|date',
        ]);

        $activity = CrmLeadActivity::create([
            ...$request->only('type', 'subject', 'description', 'activity_at'),
            'lead_id'    => $lead->id,
            'created_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Activity logged.', 'activity' => $activity]);
    }

    public function convert(Request $request, CrmLead $lead)
    {
        if ($lead->status === 'lost') {
            return response()->json(['success' => false, 'message' => 'Cannot convert a lost lead.'], 422);
        }

        DB::transaction(function () use ($lead, $request) {
            $customer = CrmCustomer::create([
                'name'    => $lead->company ?? $lead->name,
                'email'   => $lead->email,
                'phone'   => $lead->phone,
                'notes'   => "Converted from Lead #{$lead->id}: {$lead->name}",
            ]);

            $lead->update([
                'status'               => 'won',
                'converted_customer_id'=> $customer->id,
                'converted_at'         => now(),
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Lead converted to customer.']);
    }
}
