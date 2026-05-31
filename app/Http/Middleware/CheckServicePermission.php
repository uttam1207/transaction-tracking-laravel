<?php

namespace App\Http\Middleware;

use App\Models\ServicePermission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckServicePermission
{
    public function handle(Request $request, Closure $next, string $serviceKey): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!ServicePermission::canAccess($serviceKey, $user)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You do not have access to this service.'], 403);
            }
            abort(403, 'You do not have access to this service.');
        }

        return $next($request);
    }
}
