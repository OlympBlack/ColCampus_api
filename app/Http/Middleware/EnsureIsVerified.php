<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnsureIsVerified
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if (!$user || !$user->isKycValidated()) {
            return new JsonResponse([
                'message' => 'Votre compte doit etre valide par l administration avant d acceder a cette action.',
                'verification_required' => true,
            ], 403);
        }

        return $next($request);
    }
}
