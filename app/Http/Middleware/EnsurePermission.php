<?php

namespace App\Http\Middleware;

use App\Support\AccessControl;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $userId = (int) ($request->user()?->id ?? 0);
        $permissions = array_values(array_filter($permissions, static fn (string $permission): bool => $permission !== ''));

        if ($permissions === []) {
            return $next($request);
        }

        foreach ($permissions as $permission) {
            if (AccessControl::can($userId, $permission)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error' => 'Anda tidak memiliki akses untuk aksi ini.',
            ], 403);
        }

        abort(403, 'Anda tidak memiliki akses untuk halaman ini.');
    }
}
