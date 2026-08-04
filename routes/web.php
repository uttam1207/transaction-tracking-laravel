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
use App\Http\Controllers\Admin\WalletController;
use App\Http\Controllers\Admin\EmployeeWalletController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\FeedController;
use App\Http\Controllers\Admin\AnimalController;
use App\Http\Controllers\Admin\MilkController;
use App\Http\Controllers\Admin\BreedingController;
use App\Http\Controllers\Admin\HealthController;
use App\Http\Controllers\Admin\FarmController;
use App\Http\Controllers\Admin\CrmController;
use App\Http\Controllers\Admin\FranchiseController;
use App\Http\Controllers\Admin\ProcurementController;
use App\Http\Controllers\Admin\SalesModuleController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\ComplianceController;
use App\Http\Controllers\Admin\ReportCenterController;
use App\Http\Controllers\Admin\ServicePermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Employee\TaskController as EmployeeTaskController;
use App\Http\Controllers\Employee\WorkReportController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\QuestionController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

// ─── TEMPORARY: run pending migrations via browser ───────────────────────────
// Visit: /run-migrations?token=asdairy-migrate-2026
// REMOVE THIS ROUTE after the migration has been applied on the server.
Route::get('/run-migrations', function (\Illuminate\Http\Request $request) {
    if ($request->query('token') !== 'asdairy-migrate-2026') {
        abort(403);
    }
    Artisan::call('migrate', ['--force' => true]);
    $migrateOutput = Artisan::output();

    Artisan::call('db:seed', ['--class' => 'ASDairyMasterSeeder', '--force' => true]);
    $seedOutput = Artisan::output();

    return '<pre>--- MIGRATIONS ---\n' . $migrateOutput . "\n--- SEEDER ---\n" . $seedOutput . '</pre>';
});
// ─────────────────────────────────────────────────────────────────────────────

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

// Q&A — accessible by all authenticated users
Route::middleware(['auth', 'check.status'])->prefix('qa')->name('questions.')->group(function () {
    Route::get('/',                                              [QuestionController::class, 'index'])->name('index');
    Route::get('/create',                                        [QuestionController::class, 'create'])->name('create');
    Route::post('/',                                             [QuestionController::class, 'store'])->name('store');
    Route::get('/{question}',                                    [QuestionController::class, 'show'])->name('show');
    Route::get('/{question}/edit',                               [QuestionController::class, 'edit'])->name('edit');
    Route::put('/{question}',                                    [QuestionController::class, 'update'])->name('update');
    Route::delete('/{question}',                                 [QuestionController::class, 'destroy'])->name('destroy');
    Route::post('/{question}/answers',                           [QuestionController::class, 'storeAnswer'])->name('answers.store');
    Route::put('/{question}/answers/{answer}',                   [QuestionController::class, 'updateAnswer'])->name('answers.update');
    Route::delete('/{question}/answers/{answer}',                [QuestionController::class, 'destroyAnswer'])->name('answers.destroy');
    Route::post('/{question}/answers/{answer}/accept',           [QuestionController::class, 'acceptAnswer'])->name('answers.accept');
});

// Documents — accessible by all authenticated users
Route::middleware(['auth', 'check.status'])->group(function () {
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
});

// Notification routes — all authenticated users (web session, no Sanctum needed)
Route::middleware(['auth', 'check.status'])->group(function () {
    Route::get('/notifications', function () {
        $user = auth()->user();
        $notifications = $user->appNotifications()->latest()->take(20)->get();
        return response()->json([
            'success'      => true,
            'data'         => $notifications,
            'unread_count' => $user->appNotifications()->where('is_read', false)->count(),
        ]);
    })->name('notifications.index');

    Route::post('/notifications/{id}/read', function (int $id) {
        $n = auth()->user()->appNotifications()->find($id);
        if ($n) $n->update(['is_read' => true, 'read_at' => now()]);
        return response()->json(['success' => true]);
    })->name('notifications.read');

    Route::post('/notifications/read-all', function () {
        auth()->user()->appNotifications()->where('is_read', false)
              ->update(['is_read' => true, 'read_at' => now()]);
        return response()->json(['success' => true]);
    })->name('notifications.read-all');
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

    // Manager Dashboard
    Route::get('/manager-dashboard', [ManagerDashboardController::class, 'index'])->name('manager.dashboard');

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

    // Transaction Management — import routes MUST come before resource to avoid {transaction} catch-all
    Route::get('/transactions/import',     [TransactionController::class, 'importForm'])->name('transactions.import');
    Route::post('/transactions/import',    [TransactionController::class, 'processImport'])->name('transactions.import.process');
    Route::get('/transactions/sample-csv', [TransactionController::class, 'sampleCsv'])->name('transactions.sample-csv');
    Route::resource('transactions', TransactionController::class);
    Route::post('/transactions/{transaction}/status', [TransactionController::class, 'updateStatus'])->name('transactions.status');
    Route::get('/transactions/{transaction}/receipt', [TransactionController::class, 'downloadPdf'])->name('transactions.receipt');
    Route::post('/transactions/{transaction}/refund', [TransactionController::class, 'refund'])->name('transactions.refund');
    Route::post('/transactions/batch-update', [TransactionController::class, 'batchUpdate'])->name('transactions.batch-update');
    Route::get('/transactions-export/csv', [TransactionController::class, 'exportCsv'])->name('transactions.export.csv');
    Route::get('/transactions-export/pdf', [TransactionController::class, 'exportPdf'])->name('transactions.export.pdf');
    Route::get('/transactions-export/excel', [TransactionController::class, 'exportExcel'])->name('transactions.export.excel');
    Route::get('/transactions/{transaction}/voucher', [TransactionController::class, 'voucherPdf'])->name('transactions.voucher');
    Route::get('/voucher/blank', [TransactionController::class, 'blankVoucher'])->name('transactions.voucher.blank');

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
    Route::get('/reports/financial-summary', [ReportController::class, 'financialSummary'])->name('reports.financial-summary');
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
    Route::post('/shifts/bulk-assign', [ShiftController::class, 'bulkAssign'])->name('shifts.bulk-assign');
    Route::patch('/shifts/employee/{employee}', [ShiftController::class, 'updateShift'])->name('shifts.update');
    // Shift Type CRUD
    Route::post('/shifts/types', [ShiftController::class, 'store'])->name('shifts.types.store');
    Route::patch('/shifts/types/{shift}', [ShiftController::class, 'updateShiftType'])->name('shifts.types.update');
    Route::delete('/shifts/types/{shift}', [ShiftController::class, 'destroyShiftType'])->name('shifts.types.destroy');

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

    // Expense Management (Module 8)
    Route::resource('expenses', ExpenseController::class);
    Route::get('/expenses-export/excel', [ExpenseController::class, 'exportExcel'])->name('expenses.export.excel');
    Route::get('/expenses-export/csv', [ExpenseController::class, 'exportCsv'])->name('expenses.export.csv');
    Route::get('/expenses-export/pdf', [ExpenseController::class, 'exportPdf'])->name('expenses.export.pdf');

    // Stock Management (Module 9)
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/in', [StockController::class, 'stockInForm'])->name('stock.in');
    Route::post('/stock/in', [StockController::class, 'storeStockIn'])->name('stock.in.store');
    Route::get('/stock/out', [StockController::class, 'stockOutForm'])->name('stock.out');
    Route::post('/stock/out', [StockController::class, 'storeStockOut'])->name('stock.out.store');
    Route::get('/stock/adjustment', [StockController::class, 'adjustmentForm'])->name('stock.adjustment');
    Route::post('/stock/adjustment', [StockController::class, 'storeAdjustment'])->name('stock.adjustment.store');
    Route::get('/stock/movements', [StockController::class, 'movements'])->name('stock.movements');
    Route::get('/stock/export/pdf', [StockController::class, 'exportPdf'])->name('stock.export.pdf');

    // Feed Auto Calculation & Alerts
    Route::get('/feed/calculator', [FeedController::class, 'calculator'])->name('feed.calculator');
    Route::post('/feed/groups/{group}', [FeedController::class, 'updateGroupCount'])->name('feed.groups.update');
    Route::post('/feed/plans', [FeedController::class, 'updateFeedPlan'])->name('feed.plans.update');

    // Module 2 — Animal Management
    Route::resource('animals', AnimalController::class);
    Route::post('/animals/{animal}/actions', [AnimalController::class, 'storeAction'])->name('animals.actions.store');

    // Module 3 — Milk Management
    Route::get('/milk', [MilkController::class, 'index'])->name('milk.index');
    Route::post('/milk', [MilkController::class, 'store'])->name('milk.store');

    // Module 4 — Breeding Management
    Route::get('/breeding', [BreedingController::class, 'index'])->name('breeding.index');
    Route::post('/breeding', [BreedingController::class, 'store'])->name('breeding.store');

    // Module 5 — Health Management
    Route::get('/health', [HealthController::class, 'index'])->name('health.index');
    Route::post('/health', [HealthController::class, 'store'])->name('health.store');

    // Module 7 — Farm Management
    Route::get('/farm', [FarmController::class, 'index'])->name('farm.index');
    Route::post('/farm', [FarmController::class, 'store'])->name('farm.store');

    // Module 11 — CRM
    Route::get('/crm', [CrmController::class, 'index'])->name('crm.index');
    Route::post('/crm', [CrmController::class, 'store'])->name('crm.store');

    // Module 12 — Franchise Management
    Route::get('/franchise', [FranchiseController::class, 'index'])->name('franchise.index');
    Route::post('/franchise', [FranchiseController::class, 'store'])->name('franchise.store');

    // Module 13 — Procurement
    Route::get('/procurement', [ProcurementController::class, 'index'])->name('procurement.index');
    Route::post('/procurement', [ProcurementController::class, 'store'])->name('procurement.store');

    // Module 14 — Sales
    Route::get('/sales', [SalesModuleController::class, 'index'])->name('sales.index');
    Route::post('/sales', [SalesModuleController::class, 'store'])->name('sales.store');

    // Module 15 — Maintenance
    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('/maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');

    // Module 16 — Compliance
    Route::get('/compliance', [ComplianceController::class, 'index'])->name('compliance.index');
    Route::post('/compliance', [ComplianceController::class, 'store'])->name('compliance.store');

    // Module 17 — Reports Center
    Route::get('/report-center', [ReportCenterController::class, 'center'])->name('reports.center');

    // Settings
    Route::get('/settings/{group?}', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/{group}', [SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/test-smtp', [SettingController::class, 'testSmtp'])->name('settings.test-smtp');

    // Wallet Management & Service Permissions — Super Admin only
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/wallets', [WalletController::class, 'index'])->name('wallets.index');
        Route::post('/wallets/{wallet}/add-money', [WalletController::class, 'addMoney'])->name('wallets.addMoney');
        Route::patch('/wallets/{wallet}/toggle-freeze', [WalletController::class, 'toggleFreeze'])->name('wallets.toggleFreeze');

        Route::get('/permissions', [ServicePermissionController::class, 'index'])->name('permissions.index');
        Route::put('/permissions/{servicePermission}', [ServicePermissionController::class, 'update'])->name('permissions.update');

        // Role Management
        Route::resource('roles', RoleController::class)->except(['show']);
    });
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

    // My Wallet
    Route::get('/wallet', [EmployeeWalletController::class, 'index'])->name('wallet.index');
});
