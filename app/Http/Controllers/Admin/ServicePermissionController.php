<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\ServicePermission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServicePermissionController extends Controller
{
    public function index()
    {
        $services = ServicePermission::orderBy('sort_order')->get();
        $roles    = Role::where('is_active', true)
                        ->where('name', '!=', 'super_admin')
                        ->orderBy('sort_order')
                        ->orderBy('id')
                        ->get();

        return view('admin.permissions.index', compact('services', 'roles'));
    }

    public function update(Request $request, ServicePermission $servicePermission)
    {
        $validRoles = Role::where('is_active', true)
                         ->where('name', '!=', 'super_admin')
                         ->pluck('name')
                         ->toArray();

        $request->validate([
            'allowed_roles'   => 'present|array',
            'allowed_roles.*' => Rule::in($validRoles),
        ]);

        $servicePermission->update(['allowed_roles' => $request->allowed_roles]);
        ServicePermission::clearCache($servicePermission->service_key);

        return response()->json(['success' => true, 'message' => 'Permissions updated.']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_name' => 'required|string|max:100',
            'service_key'  => 'required|string|max:100|unique:service_permissions,service_key|regex:/^[a-z0-9_]+$/',
            'description'  => 'nullable|string|max:255',
            'icon'         => 'nullable|string|max:60',
            'sort_order'   => 'nullable|integer|min:0',
        ]);

        $service = ServicePermission::create([
            'service_name'  => $request->service_name,
            'service_key'   => $request->service_key,
            'description'   => $request->description,
            'icon'          => $request->icon ?: 'grid',
            'allowed_roles' => [],
            'sort_order'    => $request->sort_order ?? 99,
        ]);

        return response()->json(['success' => true, 'message' => 'Service added.', 'id' => $service->id]);
    }

    public function updateMeta(Request $request, ServicePermission $servicePermission)
    {
        $request->validate([
            'service_name' => 'required|string|max:100',
            'description'  => 'nullable|string|max:255',
            'icon'         => 'nullable|string|max:60',
            'sort_order'   => 'nullable|integer|min:0',
            'is_active'    => 'boolean',
        ]);

        $servicePermission->update([
            'service_name' => $request->service_name,
            'description'  => $request->description,
            'icon'         => $request->icon ?: 'grid',
            'sort_order'   => $request->sort_order ?? $servicePermission->sort_order,
            'is_active'    => $request->boolean('is_active', $servicePermission->is_active),
        ]);

        ServicePermission::clearCache($servicePermission->service_key);

        return response()->json(['success' => true, 'message' => 'Service updated.']);
    }

    public function destroy(ServicePermission $servicePermission)
    {
        ServicePermission::clearCache($servicePermission->service_key);
        $servicePermission->delete();

        return response()->json(['success' => true, 'message' => 'Service deleted.']);
    }
}
