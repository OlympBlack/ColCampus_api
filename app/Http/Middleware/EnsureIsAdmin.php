<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if (!$user || !$user->isAdmin()) {
            return new JsonResponse([
                'message' => 'Cette action est reservee aux administrateurs.',
            ], 403);
        }

        return $next($request);
    }
}
