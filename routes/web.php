<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\FraudAlertController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\QueueMonitorController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\TimesheetController;
use App\Http\Controllers\Admin\WorkReportController as AdminWorkReportController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Employee\TaskController as EmployeeTaskController;
use App\Http\Controllers\Employee\WorkReportController;
use Illuminate\Support\Facades\Route;

// Auth Routes (Guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
    Route::get('/2fa', [LoginController::class, 'showTwoFactor'])->name('auth.2fa');
    Route::post('/2fa', [LoginController::class, 'verifyTwoFactor']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Email Verification
Route::middleware(['auth'])->group(function () {
    Route::get('/email/verify', fn() => view('auth.verify-email'))->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect(auth()->user()->getDashboardRoute())->with('success', 'Email verified!');
    })->middleware(['signed'])->name('verification.verify');
    Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('success', 'Verification link sent!');
    })->middleware(['throttle:6,1'])->name('verification.send');
});

// Root redirect
Route::get('/', function () {
    return auth()->check() ? redirect(auth()->user()->getDashboardRoute()) : redirect()->route('login');
});

// ============================================================
// ADMIN PANEL ROUTES
// ============================================================
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'check.status', 'role:super_admin,admin,manager,auditor'])
    ->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('/dashboard/chart', [DashboardController::class, 'getChartData'])->name('dashboard.chart');

    // User Management
    Route::resource('users', UserController::class);
    Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('/users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');

    // Employee Management
    Route::resource('employees', EmployeeController::class);
    Route::get('/employees/{employee}/performance', [EmployeeController::class, 'performance'])->name('employees.performance');
    Route::get('/employees/export/excel', [EmployeeController::class, 'exportExcel'])->name('employees.export.excel');
    Route::get('/employees/export/csv', [EmployeeController::class, 'exportCsv'])->name('employees.export.csv');
    Route::get('/employees/import/template', [EmployeeController::class, 'importTemplate'])->name('employees.import.template');
    Route::post('/employees/import', [EmployeeController::class, 'import'])->name('employees.import');

    // Transaction Management
    Route::resource('transactions', TransactionController::class)->except(['edit']);
    Route::post('/transactions/{transaction}/status', [TransactionController::class, 'updateStatus'])->name('transactions.status');
    Route::get('/transactions-export/csv', [TransactionController::class, 'exportCsv'])->name('transactions.export.csv');
    Route::get('/transactions-export/pdf', [TransactionController::class, 'exportPdf'])->name('transactions.export.pdf');
    Route::get('/transactions-export/excel', [TransactionController::class, 'exportExcel'])->name('transactions.export.excel');

    // Fraud Alerts
    Route::get('/fraud-alerts', [FraudAlertController::class, 'index'])->name('fraud-alerts.index');
    Route::get('/fraud-alerts/rules', [FraudAlertController::class, 'rules'])->name('fraud-alerts.rules');
    Route::post('/fraud-alerts/rules', [FraudAlertController::class, 'storeRule'])->name('fraud-alerts.rules.store');
    Route::get('/fraud-alerts/blacklist', [FraudAlertController::class, 'blacklist'])->name('fraud-alerts.blacklist');
    Route::post('/fraud-alerts/blacklist', [FraudAlertController::class, 'addToBlacklist'])->name('fraud-alerts.blacklist.store');
    Route::get('/fraud-alerts/{fraudAlert}', [FraudAlertController::class, 'show'])->name('fraud-alerts.show');
    Route::post('/fraud-alerts/{fraudAlert}/status', [FraudAlertController::class, 'updateStatus'])->name('fraud-alerts.status');
    Route::post('/fraud-alerts/{fraudAlert}/assign', [FraudAlertController::class, 'assign'])->name('fraud-alerts.assign');

    // Attendance Management
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');
    Route::get('/attendance/leaves', [AttendanceController::class, 'leaves'])->name('attendance.leaves');
    Route::post('/attendance/leaves/{leave}/approve', [AttendanceController::class, 'approveLeave'])->name('attendance.leaves.approve');

    // Task Management
    Route::resource('tasks', TaskController::class)->except(['create', 'show', 'edit']);
    Route::get('/tasks/kanban', [TaskController::class, 'kanban'])->name('tasks.kanban');
    Route::post('/tasks/{task}/approve', [TaskController::class, 'approve'])->name('tasks.approve');
    Route::post('/tasks/{task}/reject', [TaskController::class, 'reject'])->name('tasks.reject');

    // Reports
    Route::get('/reports/transactions', [ReportController::class, 'transactionReport'])->name('reports.transactions');
    Route::get('/reports/employees', [ReportController::class, 'employeeReport'])->name('reports.employees');
    Route::get('/reports/attendance', [ReportController::class, 'attendanceReport'])->name('reports.attendance');
    Route::get('/reports/{type}/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
    Route::get('/reports/audit-logs', [ReportController::class, 'auditLogs'])->name('reports.audit-logs');

    // Department Management
    Route::resource('departments', DepartmentController::class)->only(['index', 'store', 'update', 'destroy']);

    // Holiday Management
    Route::resource('holidays', HolidayController::class)->only(['index', 'store', 'update', 'destroy']);

    // Project Management
    Route::resource('projects', ProjectController::class)->except(['create', 'edit']);

    // Timesheet Management
    Route::get('/timesheets', [TimesheetController::class, 'index'])->name('timesheets.index');
    Route::post('/timesheets/{timesheet}/approve', [TimesheetController::class, 'approve'])->name('timesheets.approve');
    Route::post('/timesheets/{timesheet}/reject', [TimesheetController::class, 'reject'])->name('timesheets.reject');
    Route::post('/timesheets/bulk-approve', [TimesheetController::class, 'bulkApprove'])->name('timesheets.bulk-approve');

    // Team Management
    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::post('/teams/assign', [TeamController::class, 'assignTeam'])->name('teams.assign');
    Route::delete('/teams/{employee}/remove', [TeamController::class, 'removeFromTeam'])->name('teams.remove');

    // Shift Management
    Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
    Route::patch('/shifts/{employee}', [ShiftController::class, 'updateShift'])->name('shifts.update');
    Route::post('/shifts/bulk-assign', [ShiftController::class, 'bulkAssign'])->name('shifts.bulk-assign');

    // Work Report Review (Admin/Manager)
    Route::get('/work-reports', [AdminWorkReportController::class, 'index'])->name('work-reports.index');
    Route::get('/work-reports/{workReport}', [AdminWorkReportController::class, 'show'])->name('work-reports.show');
    Route::post('/work-reports/{workReport}/approve', [AdminWorkReportController::class, 'approve'])->name('work-reports.approve');
    Route::post('/work-reports/{workReport}/reject', [AdminWorkReportController::class, 'reject'])->name('work-reports.reject');
    Route::post('/work-reports/bulk-approve', [AdminWorkReportController::class, 'bulkApprove'])->name('work-reports.bulk-approve');

    // Queue Monitor
    Route::get('/queue', [QueueMonitorController::class, 'index'])->name('queue.index');
    Route::post('/queue/{uuid}/retry', [QueueMonitorController::class, 'retry'])->name('queue.retry');
    Route::post('/queue/retry-all', [QueueMonitorController::class, 'retryAll'])->name('queue.retry-all');
    Route::delete('/queue/failed/{uuid}', [QueueMonitorController::class, 'deleteFailedJob'])->name('queue.delete-failed');
    Route::post('/queue/flush', [QueueMonitorController::class, 'flushFailed'])->name('queue.flush');

    // Global Search
    Route::get('/search', [SearchController::class, 'search'])->name('search');

    // Settings
    Route::get('/settings/{group?}', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/{group}', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/test-smtp', [SettingController::class, 'testSmtp'])->name('settings.test-smtp');
});

// ============================================================
// EMPLOYEE PANEL ROUTES
// ============================================================
Route::prefix('employee')
    ->name('employee.')
    ->middleware(['auth', 'check.status'])
    ->group(function () {

    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');

    // Attendance
    Route::get('/attendance', [EmployeeAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in', [EmployeeAttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out', [EmployeeAttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::get('/attendance/leaves', [EmployeeAttendanceController::class, 'leaveIndex'])->name('attendance.leaves');
    Route::post('/attendance/leaves', [EmployeeAttendanceController::class, 'requestLeave'])->name('attendance.leaves.store');

    // Tasks
    Route::get('/tasks', [EmployeeTaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{task}', [EmployeeTaskController::class, 'show'])->name('tasks.show');
    Route::post('/tasks/{task}/status', [EmployeeTaskController::class, 'updateStatus'])->name('tasks.status');
    Route::post('/tasks/{task}/comments', [EmployeeTaskController::class, 'addComment'])->name('tasks.comments.store');
    Route::post('/tasks/{task}/timer/start', [EmployeeTaskController::class, 'startTimer'])->name('tasks.timer.start');
    Route::post('/tasks/{task}/timer/stop', [EmployeeTaskController::class, 'stopTimer'])->name('tasks.timer.stop');

    // Work Reports
    Route::get('/work-reports', [WorkReportController::class, 'index'])->name('work-reports.index');
    Route::get('/work-reports/create', [WorkReportController::class, 'create'])->name('work-reports.create');
    Route::post('/work-reports', [WorkReportController::class, 'store'])->name('work-reports.store');
    Route::get('/work-reports/{report}', [WorkReportController::class, 'show'])->name('work-reports.show');
    Route::post('/work-reports/{report}/submit', [WorkReportController::class, 'submit'])->name('work-reports.submit');

    // Profile
    Route::get('/profile', fn() => view('employee.profile', ['user' => auth()->user()]))->name('profile');
});
