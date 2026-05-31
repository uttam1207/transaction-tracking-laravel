<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServicePermission;
use Illuminate\Http\Request;

class ServicePermissionController extends Controller
{
    private const ROLES = ['admin', 'manager', 'auditor', 'viewer', 'employee'];

    public function index()
    {
        $services = ServicePermission::orderBy('sort_order')->get();
        $roles    = self::ROLES;

        return view('admin.permissions.index', compact('services', 'roles'));
    }

    public function update(Request $request, ServicePermission $servicePermission)
    {
        $request->validate([
            'allowed_roles' => 'present|array',
            'allowed_roles.*' => 'in:admin,manager,auditor,viewer,employee',
        ]);

        $servicePermission->update(['allowed_roles' => $request->allowed_roles]);
        ServicePermission::clearCache($servicePermission->service_key);

        return response()->json(['success' => true, 'message' => 'Permissions updated.']);
    }
}
