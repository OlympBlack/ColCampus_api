<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Support\ApiData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if (!$user->is_verified) {
                Auth::logout();

                return response()->json([
                    'message' => 'Votre compte n est pas encore verifie. Veuillez valider votre email avec le code OTP.',
                    'verification_required' => true,
                ], 403);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Connexion reussie',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => ApiData::user($user),
            ], 200);
        }

        return response()->json(['message' => 'Identifiants invalides'], 401);
    }

    /**
     * Terminer la session de l'utilisateur.
     */
    public function logout(): JsonResponse
    {
        Auth::user()->tokens()->delete();

        return response()->json([
            'message' => 'Deconnexion reussie',
        ]);
    }
}
