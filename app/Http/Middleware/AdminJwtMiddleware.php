<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class AdminJwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $config = config('admin.jwt');

        if (empty($config['secret'])) {
            throw new RuntimeException('Admin JWT secret is not configured.');
        }

        $authorization = $request->header('Authorization');

        if (!$authorization || !str_starts_with($authorization, 'Bearer ')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $token = substr($authorization, 7);

        try {
            $decoded = JWT::decode($token, new Key($config['secret'], $config['algo'] ?? 'HS256'));
        } catch (\Firebase\JWT\ExpiredException $e) {
            return response()->json(['message' => 'Token expired'], 401);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $adminId = $decoded->sub ?? null;
        $role = $decoded->role ?? null;

        if (!$adminId || !$role) {
            return response()->json(['message' => 'Invalid token payload'], 401);
        }

        $admin = Admin::find($adminId);

        if (!$admin || !$admin->is_active) {
            return response()->json(['message' => 'Account not available'], 401);
        }

        if (!in_array($role, ['admin', 'super_admin'])) {
            return response()->json(['message' => 'Not authorized'], 403);
        }

        Auth::setUser($admin);
        $request->setUserResolver(fn () => $admin);

        return $next($request);
    }
}
