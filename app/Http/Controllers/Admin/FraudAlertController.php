<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FraudAlert;
use App\Models\FraudRule;
use App\Models\Blacklist;
use Illuminate\Http\Request;

class FraudAlertController extends Controller
{
    public function index(Request $request)
    {
        $query = FraudAlert::with('transaction.user', 'assignee');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->severity) {
            $query->where('severity', $request->severity);
        }

        $alerts = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'open' => FraudAlert::where('status', 'open')->count(),
            'investigating' => FraudAlert::where('status', 'investigating')->count(),
            'resolved' => FraudAlert::where('status', 'resolved')->count(),
            'critical' => FraudAlert::where('severity', 'critical')->where('status', 'open')->count(),
        ];

        return view('admin.fraud-alerts.index', compact('alerts', 'stats'));
    }

    public function show(FraudAlert $fraudAlert)
    {
        $fraudAlert->load('transaction.user', 'transaction.logs', 'assignee', 'resolver');
        return view('admin.fraud-alerts.show', compact('fraudAlert'));
    }

    public function updateStatus(Request $request, FraudAlert $fraudAlert)
    {
        $request->validate([
            'status' => 'required|in:open,investigating,resolved,false_positive',
            'resolution_notes' => 'nullable|string',
        ]);

        $fraudAlert->update([
            'status' => $request->status,
            'resolution_notes' => $request->resolution_notes,
            'resolved_by' => in_array($request->status, ['resolved', 'false_positive']) ? auth()->id() : null,
            'resolved_at' => in_array($request->status, ['resolved', 'false_positive']) ? now() : null,
        ]);

        return response()->json(['success' => true, 'message' => 'Alert status updated.']);
    }

    public function assign(Request $request, FraudAlert $fraudAlert)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $fraudAlert->update(['assigned_to' => $request->user_id, 'status' => 'investigating']);
        return response()->json(['success' => true, 'message' => 'Alert assigned.']);
    }

    // Fraud Rules Management
    public function rules()
    {
        $rules = FraudRule::orderBy('priority')->paginate(15);
        return view('admin.fraud-alerts.rules', compact('rules'));
    }

    public function storeRule(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:fraud_rules',
            'type' => 'required|in:amount,velocity,duplicate,blacklist,geo,device,pattern',
            'conditions' => 'required|json',
            'action' => 'required|in:flag,block,alert,review',
            'risk_score' => 'required|integer|min:0|max:100',
            'severity' => 'required|in:low,medium,high,critical',
            'priority' => 'required|integer|min:1',
        ]);

        FraudRule::create(array_merge(
            $request->only(['name', 'code', 'description', 'type', 'action', 'risk_score', 'severity', 'priority']),
            ['conditions' => json_decode($request->conditions, true), 'created_by' => auth()->id()]
        ));

        return response()->json(['success' => true, 'message' => 'Fraud rule created.']);
    }

    // Blacklist Management
    public function blacklist(Request $request)
    {
        $blacklist = Blacklist::with('addedBy')->latest()->paginate(15);
        return view('admin.fraud-alerts.blacklist', compact('blacklist'));
    }

    public function addToBlacklist(Request $request)
    {
        $request->validate([
            'type' => 'required|in:ip,email,account,device,country',
            'value' => 'required|string',
            'reason' => 'required|string',
        ]);

        Blacklist::updateOrCreate(
            ['type' => $request->type, 'value' => $request->value],
            ['reason' => $request->reason, 'added_by' => auth()->id(), 'is_active' => true]
        );

        return response()->json(['success' => true, 'message' => 'Added to blacklist.']);
    }
}
