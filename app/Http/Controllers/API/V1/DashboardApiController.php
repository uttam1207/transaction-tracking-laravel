<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    public function __construct(private DashboardService $dashboardService)
    {
    }

    public function adminStats(Request $request)
    {
        if (!$request->user()->isAdmin() && !$request->user()->isManager()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->getAdminStats(),
        ]);
    }

    public function employeeStats(Request $request)
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->getEmployeeDashboard($employee->id),
        ]);
    }

    public function chartData(Request $request)
    {
        $type = $request->get('type', 'transactions');
        $days = $request->get('days', 30);

        $data = match($type) {
            'transactions' => $this->dashboardService->getTransactionChartData($days),
            'attendance' => $this->dashboardService->getAttendanceChartData($days),
            'revenue' => $this->dashboardService->getMonthlyRevenue(),
            default => [],
        };

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function notifications(Request $request)
    {
        $notifications = $request->user()
            ->appNotifications()
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markNotificationRead(Request $request, int $id)
    {
        $notification = $request->user()->appNotifications()->find($id);

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $notification->markAsRead();
        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->appNotifications()->unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'All notifications marked as read.']);
    }
}
