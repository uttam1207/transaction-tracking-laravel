<?php

namespace App\Http\Middleware;

use App\Models\ServicePermission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckServicePermission
{
    /**
     * Map route-name prefixes → service_key in service_permissions table.
     * Any admin route NOT in this map passes through freely.
     */
    private const ROUTE_SERVICE_MAP = [
        'admin.transactions'       => 'transactions',
        'admin.fraud-alerts'       => 'fraud_alerts',
        'admin.wallets'            => 'wallets',
        'admin.expenses'           => 'expenses',
        'admin.expense-categories' => 'expense_categories',
        'admin.salaries'           => 'salaries',
        'admin.users'              => 'users',
        'admin.employees'          => 'employees',
        'admin.departments'        => 'departments',
        'admin.teams'              => 'teams',
        'admin.attendance'         => 'attendance',
        'admin.shifts'             => 'shifts',
        'admin.holidays'           => 'holidays',
        'admin.tasks'              => 'tasks',
        'admin.projects'           => 'projects',
        'admin.work-reports'       => 'work_reports',
        'admin.timesheets'         => 'timesheets',
        'admin.animals'            => 'animals',
        'admin.breeds'             => 'breeds',
        'admin.milk'               => 'milk',
        'admin.breeding'           => 'breeding',
        'admin.health'             => 'health',
        'admin.feed'               => 'feed',
        'admin.farm'               => 'farm',
        'admin.stock'              => 'stock',
        'admin.stock-items'        => 'stock',
        'admin.stock-categories'   => 'stock',
        'admin.stock-types'        => 'stock',
        'admin.maintenance'        => 'maintenance',
        'admin.compliance'         => 'compliance',
        'admin.contacts'           => 'contacts',
        'admin.contact-categories' => 'contact_categories',
        'admin.crm'                => 'crm',
        'admin.franchise'          => 'franchise',
        'admin.procurement'        => 'procurement',
        'admin.vendors'            => 'vendors',
        'admin.sales'              => 'sales',
        'admin.reports'            => 'reports',
        'admin.report-center'      => 'report_center',
        'admin.queue'              => 'queue',
        'admin.settings'           => 'settings',
    ];

    /**
     * Handle an incoming request.
     *
     * Called as middleware('service')        → auto-detect key from route name
     * Called as middleware('service:milk')   → explicit key override
     */
    public function handle(Request $request, Closure $next, string $serviceKey = ''): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super admin is never restricted
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        // Resolve service key: explicit param wins, otherwise auto-detect from route name
        $key = $serviceKey ?: $this->detectServiceKey($request->route()?->getName() ?? '');

        // No matching service in our map → no restriction, pass through
        if (!$key) {
            return $next($request);
        }

        if (!ServicePermission::canAccess($key, $user)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You do not have access to this service.'], 403);
            }

            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to access this module.');
        }

        return $next($request);
    }

    private function detectServiceKey(string $routeName): string
    {
        foreach (self::ROUTE_SERVICE_MAP as $prefix => $key) {
            if (str_starts_with($routeName, $prefix)) {
                return $key;
            }
        }
        return '';
    }
}
