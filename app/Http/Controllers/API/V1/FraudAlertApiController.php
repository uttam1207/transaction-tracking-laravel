<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FraudAlertResource;
use App\Models\FraudAlert;
use Illuminate\Http\Request;

class FraudAlertApiController extends Controller
{
    /**
     * @OA\Get(path="/api/v1/fraud-alerts", tags={"Fraud Alerts"}, security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="List of fraud alerts"))
     */
    public function index(Request $request)
    {
        $query = FraudAlert::with(['transaction', 'assignee']);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->severity) {
            $query->where('severity', $request->severity);
        }
        if ($request->alert_type) {
            $query->where('alert_type', $request->alert_type);
        }
        if ($request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $alerts = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => FraudAlertResource::collection($alerts),
            'meta' => [
                'total'        => $alerts->total(),
                'current_page' => $alerts->currentPage(),
                'last_page'    => $alerts->lastPage(),
            ],
        ]);
    }

    /**
     * @OA\Get(path="/api/v1/fraud-alerts/{id}", tags={"Fraud Alerts"}, security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="Fraud alert details"))
     */
    public function show(FraudAlert $fraudAlert)
    {
        $fraudAlert->load(['transaction', 'assignee', 'resolver']);

        return response()->json(['data' => new FraudAlertResource($fraudAlert)]);
    }

    /**
     * @OA\Patch(path="/api/v1/fraud-alerts/{id}", tags={"Fraud Alerts"}, security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="Fraud alert updated"))
     */
    public function update(Request $request, FraudAlert $fraudAlert)
    {
        $request->validate([
            'status'           => 'sometimes|required|in:open,investigating,resolved,dismissed',
            'assigned_to'      => 'nullable|exists:users,id',
            'resolution_notes' => 'nullable|string|max:1000',
        ]);

        $data = $request->only(['status', 'assigned_to', 'resolution_notes']);

        if (in_array($request->status, ['resolved', 'dismissed'])) {
            $data['resolved_by'] = auth()->id();
            $data['resolved_at'] = now();
        }

        $fraudAlert->update($data);

        return response()->json([
            'success' => true,
            'data'    => new FraudAlertResource($fraudAlert->fresh(['transaction', 'assignee', 'resolver'])),
        ]);
    }

    /**
     * @OA\Post(path="/api/v1/fraud-alerts/{id}/assign", tags={"Fraud Alerts"}, security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="Alert assigned"))
     */
    public function assign(Request $request, FraudAlert $fraudAlert)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $fraudAlert->update([
            'assigned_to' => $request->assigned_to,
            'status'      => $fraudAlert->status === 'open' ? 'investigating' : $fraudAlert->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Alert assigned successfully.']);
    }

    /**
     * @OA\Post(path="/api/v1/fraud-alerts/{id}/resolve", tags={"Fraud Alerts"}, security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="Alert resolved"))
     */
    public function resolve(Request $request, FraudAlert $fraudAlert)
    {
        $request->validate([
            'resolution_notes' => 'required|string|max:1000',
            'action'           => 'required|in:resolved,dismissed',
        ]);

        if (!in_array($fraudAlert->status, ['open', 'investigating'])) {
            return response()->json(['error' => 'Only open or investigating alerts can be resolved.'], 422);
        }

        $fraudAlert->update([
            'status'           => $request->action,
            'resolution_notes' => $request->resolution_notes,
            'resolved_by'      => auth()->id(),
            'resolved_at'      => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Fraud alert ' . $request->action . '.']);
    }

    /**
     * @OA\Get(path="/api/v1/fraud-alerts/statistics", tags={"Fraud Alerts"}, security={{"sanctum":{}}},
     *   @OA\Response(response=200, description="Fraud alert statistics"))
     */
    public function statistics()
    {
        $stats = [
            'total'         => FraudAlert::count(),
            'open'          => FraudAlert::where('status', 'open')->count(),
            'investigating' => FraudAlert::where('status', 'investigating')->count(),
            'resolved'      => FraudAlert::where('status', 'resolved')->count(),
            'dismissed'     => FraudAlert::where('status', 'dismissed')->count(),
            'by_severity'   => [
                'critical' => FraudAlert::where('severity', 'critical')->count(),
                'high'     => FraudAlert::where('severity', 'high')->count(),
                'medium'   => FraudAlert::where('severity', 'medium')->count(),
                'low'      => FraudAlert::where('severity', 'low')->count(),
            ],
        ];

        return response()->json(['success' => true, 'data' => $stats]);
    }
}
