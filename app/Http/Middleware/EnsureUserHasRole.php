<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Reject invalid role configurations.
        foreach ($roles as $role) {
            if (UserRole::tryFrom($role) === null) {
                throw new \InvalidArgumentException(
                    "Invalid role configured: {$role}"
                );
            }
        }

        if (!in_array($user->role, $roles, true)) {
            return response()->json([
                'message' => 'You are not authorized to access this resource.',
            ], 403);
        }

        return $next($request);
    }
}
