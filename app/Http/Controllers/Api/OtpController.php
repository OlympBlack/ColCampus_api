<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ResendOtpRequest;
use App\Http\Requests\Api\VerifyOtpRequest;
use App\Mail\SendOtpMail;
use App\Models\EmailOtp;
use App\Models\User;
use App\Support\ApiData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class OtpController extends Controller
{
    /**
     * Verify the OTP provided by the user.
     */
    public function verify(VerifyOtpRequest $request): JsonResponse
    {
        $otpEntry = EmailOtp::where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$otpEntry) {
            return response()->json(['message' => 'Code de vérification invalide.'], 400);
        }

        if ($otpEntry->expires_at->isPast()) {
            return response()->json(['message' => 'Ce code a expiré.'], 400);
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->update([
                'is_valide' => true,
                'is_verified' => true,
                'email_verified_at' => now(),
            ]);
        }

        $otpEntry->delete();

        return response()->json([
            'message' => 'Votre compte a été validé et vérifié avec succès.',
            'user' => $user ? ApiData::user($user->fresh(['profilEtudiant', 'profilDemarcheur', 'profilBoutique', 'documentsIdentite'])) : null,
        ]);
    }

    /**
     * Resend the OTP and validation link.
     */
    public function resend(ResendOtpRequest $request): JsonResponse
    {
        $this->generateAndSendOtp($request->email);

        return response()->json([
            'message' => 'Un nouvel email avec code et lien de validation a été envoyé.',
        ]);
    }

    /**
     * Generate 6-digit OTP and signed URL.
     */
    private function generateAndSendOtp(string $email): void
    {
        $otp = (string) random_int(100000, 999999);

        EmailOtp::where('email', $email)->delete();

        EmailOtp::create([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => now()->addHours(48),
        ]);

        $user = User::where('email', $email)->first();
        $validationUrl = null;

        if ($user) {
            $validationUrl = URL::temporarySignedRoute(
                'auth.validate-account',
                now()->addDays(2),
                [
                    'id' => $user->id,
                    'hash' => sha1($user->email),
                ]
            );
        }

        Mail::to($email)->send(new SendOtpMail($otp, $validationUrl, $user?->prenoms));
    }
}
