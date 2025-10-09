<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $user = $request->user();

        // Check if user has the required role
        if (!$this->hasRole($user, $role)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions. Required role: ' . $role
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if user has the required role
     */
    private function hasRole($user, string $requiredRole): bool
    {
        // Get user's role
        $userRole = $user->role;

        // Admin has access to everything
        if ($userRole === 'admin') {
            return true;
        }

        // Check exact role match
        if ($userRole === $requiredRole) {
            return true;
        }

        // Role hierarchy - higher roles can access lower role functions
        $roleHierarchy = [
            'admin' => 4,
            'franchise_owner' => 3,
            'unit_manager' => 2,
            'employee' => 1
        ];

        $userRoleLevel = $roleHierarchy[$userRole] ?? 0;
        $requiredRoleLevel = $roleHierarchy[$requiredRole] ?? 0;

        // Allow access if user role level is higher or equal
        return $userRoleLevel >= $requiredRoleLevel;
    }
}
