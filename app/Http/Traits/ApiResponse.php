<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a successful JSON response.
     *
     * Response structure:
     * {
     *   "code": 200,
     *   "success": true,
     *   "message": "...",
     *   "data": { ... }
     * }
     */
    protected function success(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'code'    => $code,
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * Return a paginated JSON response (includes pagination meta).
     *
     * Response structure:
     * {
     *   "code": 200,
     *   "success": true,
     *   "message": "...",
     *   "data": [...],
     *   "meta": { "total": 100, "per_page": 15, "current_page": 1, "last_page": 7 }
     * }
     */
    protected function paginated(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'code'    => 200,
            'success' => true,
            'message' => $message,
            'data'    => $paginator->items(),
            'meta'    => [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * Return an error JSON response.
     *
     * Response structure:
     * {
     *   "code": 422,
     *   "success": false,
     *   "message": "...",
     *   "data": null
     * }
     */
    protected function error(string $message = 'Error', int $code = 400, mixed $data = null): JsonResponse
    {
        return response()->json([
            'code'    => $code,
            'success' => false,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }
}