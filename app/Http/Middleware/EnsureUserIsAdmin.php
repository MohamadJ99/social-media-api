<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        if (
            !$user ||
            !in_array($user->role, [
                UserRole::Admin->value,
                UserRole::SuperAdmin->value,
            ], true)
        ) {
            return response()->json([
                'message' => 'You are not authorized to access this resource.',
            ], 403);
        }

        return $next($request);
    }
}
