<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterRequest;
use App\Mail\SendOtpMail;
use App\Models\EmailOtp;
use App\Models\User;
use App\Support\ApiData;
use App\Support\ProfileDataManager;
use App\Services\ServicePortefeuillePublications;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    /**
     * Handle user registration.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated, $request) {
            $user = User::create([
                'name' => $validated['name'],
                'prenoms' => $validated['prenoms'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'telephone' => $validated['telephone'],
                'role' => $validated['role'],
                'is_verified' => false,
                'statut_verification' => 'quarantaine',
                'commentaire_admin' => null,
                'profile_completed' => false,
            ]);

            ProfileDataManager::sync($user, $validated, $request);
            $user->save();

            if ($user->role === 'demarcheur') {
                app(ServicePortefeuillePublications::class)->initialiserPortefeuille($user);
            }

            $this->generateAndSendOtp($user->email);

            return $user->fresh(['profilEtudiant', 'profilDemarcheur', 'profilBoutique', 'documentsIdentite']);
        });

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Utilisateur cree avec succes. Un code de verification a ete envoye a votre adresse email.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => ApiData::user($user),
        ], 201);
    }

    /**
     * Generate and send a 6-digit OTP.
     */
    private function generateAndSendOtp(string $email): void
    {
        $otp = (string) random_int(100000, 999999);

        EmailOtp::where('email', $email)->delete();

        EmailOtp::create([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => now()->addHours(24),
        ]);

        Mail::to($email)->send(new SendOtpMail($otp));
    }
}
