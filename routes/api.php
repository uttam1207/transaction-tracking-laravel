<?php

use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\TransactionApiController;
use App\Http\Controllers\API\V1\AttendanceApiController;
use App\Http\Controllers\API\V1\DashboardApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Transaction Monitor REST API v1
|--------------------------------------------------------------------------
| Rate Limiting: 60 requests per minute for authenticated users
| Authentication: Laravel Sanctum (Bearer Token)
| Response Format: JSON
*/

// Public Auth Endpoints
Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/register', function (Request $request) {
        // Delegate to RegisterController logic
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);
        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => 'employee',
            'status' => 'pending',
        ]);
        $token = $user->createToken('API');
        return response()->json(['success' => true, 'token' => $token->plainTextToken], 201);
    })->middleware('throttle:5,1');
});

// Protected API Endpoints
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/refresh', [AuthController::class, 'refreshToken']);

    // Dashboard & Analytics
    Route::get('/dashboard/admin-stats', [DashboardApiController::class, 'adminStats']);
    Route::get('/dashboard/employee-stats', [DashboardApiController::class, 'employeeStats']);
    Route::get('/dashboard/chart-data', [DashboardApiController::class, 'chartData']);

    // Notifications
    Route::get('/notifications', [DashboardApiController::class, 'notifications']);
    Route::post('/notifications/{id}/read', [DashboardApiController::class, 'markNotificationRead']);
    Route::post('/notifications/read-all', [DashboardApiController::class, 'markAllRead']);

    // Transactions
    Route::apiResource('transactions', TransactionApiController::class)->except(['destroy']);
    Route::get('/transactions/statistics', [TransactionApiController::class, 'statistics']);

    // Attendance
    Route::get('/attendance', [AttendanceApiController::class, 'index']);
    Route::get('/attendance/today', [AttendanceApiController::class, 'today']);
    Route::post('/attendance/check-in', [AttendanceApiController::class, 'checkIn']);
    Route::post('/attendance/check-out', [AttendanceApiController::class, 'checkOut']);

    // Users (Admin only)
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::apiResource('users', \App\Http\Controllers\Admin\UserController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::apiResource('employees', \App\Http\Controllers\Admin\EmployeeController::class)->only(['index', 'show']);
        Route::get('/fraud-alerts', [\App\Http\Controllers\Admin\FraudAlertController::class, 'index']);
        Route::get('/audit-logs', [\App\Http\Controllers\Admin\ReportController::class, 'auditLogs']);
    });

    // Tasks
    Route::get('/tasks', [\App\Http\Controllers\Employee\TaskController::class, 'index']);
    Route::get('/tasks/{task}', [\App\Http\Controllers\Employee\TaskController::class, 'show']);
    Route::post('/tasks/{task}/status', [\App\Http\Controllers\Employee\TaskController::class, 'updateStatus']);

    // Work Reports
    Route::get('/work-reports', [\App\Http\Controllers\Employee\WorkReportController::class, 'index']);
    Route::post('/work-reports', [\App\Http\Controllers\Employee\WorkReportController::class, 'store']);
    Route::post('/work-reports/{report}/submit', [\App\Http\Controllers\Employee\WorkReportController::class, 'submit']);

    // Settings (read only for public)
    Route::get('/settings', function () {
        $settings = \App\Models\Setting::where('is_public', true)->get()->pluck('value', 'key');
        return response()->json(['success' => true, 'data' => $settings]);
    });
});

// API Health Check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'version' => 'v1',
        'timestamp' => now()->toISOString(),
        'app' => config('app.name'),
    ]);
});
