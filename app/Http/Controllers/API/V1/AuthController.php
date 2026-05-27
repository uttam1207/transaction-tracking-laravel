<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Info(title="Transaction Monitor API", version="1.0")
 * @OA\SecurityScheme(securityScheme="bearerAuth", type="http", scheme="bearer")
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(path="/api/v1/auth/login", tags={"Auth"},
     *   @OA\RequestBody(@OA\JsonContent(required={"email","password"},
     *     @OA\Property(property="email", type="string"),
     *     @OA\Property(property="password", type="string"))),
     *   @OA\Response(response=200, description="Success"))
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials.',
            ]);
        }

        if (!in_array($user->status, ['active'])) {
            return $this->errorResponse('Account is ' . $user->status, 403);
        }

        // Revoke previous tokens if needed
        $user->tokens()->where('name', $request->device_name ?? 'API')->delete();

        $token = $user->createToken($request->device_name ?? 'API', ['*'], now()->addDays(7));

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'is_online' => true,
        ]);

        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_successful' => true,
        ]);

        return $this->successResponse([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => now()->addDays(7)->toISOString(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'avatar_url' => $user->avatar_url,
            ],
        ], 'Login successful');
    }

    /**
     * @OA\Post(path="/api/v1/auth/logout", tags={"Auth"}, security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="Success"))
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        $request->user()->update(['is_online' => false]);

        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * @OA\Get(path="/api/v1/auth/me", tags={"Auth"}, security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="Success"))
     */
    public function me(Request $request)
    {
        $user = $request->user()->load('employee.department');
        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'avatar_url' => $user->avatar_url,
            'department' => $user->department?->name,
            'employee' => $user->employee ? [
                'employee_id' => $user->employee->employee_id,
                'designation' => $user->employee->designation,
                'performance_score' => $user->employee->performance_score,
            ] : null,
        ]);
    }

    public function refreshToken(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        $token = $request->user()->createToken('API', ['*'], now()->addDays(7));

        return $this->successResponse([
            'token' => $token->plainTextToken,
            'expires_at' => now()->addDays(7)->toISOString(),
        ]);
    }

    private function successResponse(mixed $data, string $message = 'Success', int $code = 200)
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $code);
    }

    private function errorResponse(string $message, int $code = 400)
    {
        return response()->json(['success' => false, 'message' => $message], $code);
    }
}
