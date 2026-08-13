<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $userRole = $request->user()->role;

        // Super admin has access to everything
        if ($userRole === 'super_admin') {
            return $next($request);
        }

        // Separate exclusion rules (not:roleName) from inclusion rules
        $exclusions = [];
        $inclusions = [];
        foreach ($roles as $role) {
            if (str_starts_with($role, 'not:')) {
                $exclusions[] = substr($role, 4);
            } else {
                $inclusions[] = $role;
            }
        }

        // If user's role is explicitly excluded, deny
        if (in_array($userRole, $exclusions)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            abort(403, 'You do not have permission to access this resource.');
        }

        // If there are inclusion rules, user must match one
        if (!empty($inclusions)) {
            foreach ($inclusions as $role) {
                if ($userRole === $role) {
                    return $next($request);
                }
            }
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            abort(403, 'You do not have permission to access this resource.');
        }

        // Only exclusion rules were given (no inclusion list) → allow anyone not excluded
        return $next($request);
    }
}
