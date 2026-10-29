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
            return response()->json(['message' => 'Code de verification invalide.'], 400);
        }

        if ($otpEntry->expires_at->isPast()) {
            return response()->json(['message' => 'Ce code a expire.'], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->update([
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        $otpEntry->delete();

        return response()->json([
            'message' => 'Votre compte a ete verifie avec succes.',
            'user' => ApiData::user($user->fresh(['profilEtudiant', 'profilDemarcheur', 'profilBoutique', 'documentsIdentite'])),
        ]);
    }

    /**
     * Resend the OTP.
     */
    public function resend(ResendOtpRequest $request): JsonResponse
    {
        $this->generateAndSendOtp($request->email);

        return response()->json([
            'message' => 'Un nouveau code de verification a ete envoye a votre adresse email.',
        ]);
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
