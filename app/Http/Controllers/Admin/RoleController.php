<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\ServicePermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        try {
            $roles = Role::orderBy('sort_order')->orderBy('id')->get()->map(function ($role) {
                $role->users_count = User::where('role', $role->name)->count();
                return $role;
            });
        } catch (\Throwable $e) {
            // Fallback if migration hasn't run yet on this environment
            $roles = Role::orderBy('id')->get()->map(function ($role) {
                $role->users_count = User::where('role', $role->name)->count();
                return $role;
            });
        }

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $services = ServicePermission::orderBy('sort_order')->get();
        return view('admin.roles.create', compact('services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'display_name' => 'required|string|max:100',
            'description'  => 'nullable|string|max:255',
            'color'        => 'required|string|max:20',
            'icon'         => 'required|string|max:50',
        ]);

        $name = Str::slug($request->display_name, '_');

        // Ensure uniqueness
        $base = $name;
        $i = 2;
        while (Role::where('name', $name)->exists()) {
            $name = $base . '_' . $i++;
        }

        $role = Role::create([
            'name'         => $name,
            'guard_name'   => 'web',
            'display_name' => $request->display_name,
            'description'  => $request->description,
            'color'        => $request->color,
            'icon'         => $request->icon,
            'is_system'    => false,
            'is_active'    => true,
            'sort_order'   => Role::max('sort_order') + 1,
        ]);

        // Assign service permissions for new role
        if ($request->has('service_permissions')) {
            foreach ($request->service_permissions as $serviceId) {
                $svc = ServicePermission::find($serviceId);
                if ($svc) {
                    $allowed = $svc->allowed_roles ?? [];
                    if (!in_array($role->name, $allowed)) {
                        $allowed[] = $role->name;
                        $svc->update(['allowed_roles' => $allowed]);
                    }
                }
            }
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role "' . $role->display_name . '" created successfully.');
    }

    public function edit(Role $role)
    {
        $services = ServicePermission::orderBy('sort_order')->get();

        // Collect which service IDs this role has access to
        $grantedServiceIds = $services->filter(function ($svc) use ($role) {
            return in_array($role->name, $svc->allowed_roles ?? []);
        })->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'services', 'grantedServiceIds'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'display_name' => 'required|string|max:100',
            'description'  => 'nullable|string|max:255',
            'color'        => 'required|string|max:20',
            'icon'         => 'required|string|max:50',
        ]);

        $role->update([
            'display_name' => $request->display_name,
            'description'  => $request->description,
            'color'        => $request->color,
            'icon'         => $request->icon,
            'is_active'    => $request->boolean('is_active', true),
        ]);

        // Sync service permissions for this role
        $granted = $request->service_permissions ?? [];

        foreach (ServicePermission::all() as $svc) {
            $allowed = $svc->allowed_roles ?? [];
            $hasAccess = in_array((string) $svc->id, array_map('strval', $granted));

            if ($hasAccess && !in_array($role->name, $allowed)) {
                $allowed[] = $role->name;
                $svc->update(['allowed_roles' => $allowed]);
            } elseif (!$hasAccess && in_array($role->name, $allowed)) {
                $svc->update(['allowed_roles' => array_values(array_filter($allowed, fn($r) => $r !== $role->name))]);
            }
        }

        // Clear service permission cache
        ServicePermission::clearCache();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role "' . $role->display_name . '" updated.');
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return back()->with('error', 'System roles cannot be deleted.');
        }

        $usersCount = User::where('role', $role->name)->count();
        if ($usersCount > 0) {
            return back()->with('error', 'Cannot delete role — ' . $usersCount . ' user(s) are assigned to it. Reassign them first.');
        }

        // Remove this role from all service permissions
        foreach (ServicePermission::all() as $svc) {
            $allowed = array_values(array_filter($svc->allowed_roles ?? [], fn($r) => $r !== $role->name));
            $svc->update(['allowed_roles' => $allowed]);
        }

        ServicePermission::clearCache();
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted.');
    }
}
