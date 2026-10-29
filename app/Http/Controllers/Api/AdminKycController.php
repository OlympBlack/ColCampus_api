<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ReviewKycRequest;
use App\Models\User;
use App\Notifications\KycReviewedNotification;
use App\Support\ApiData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminKycController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('statut_verification', 'pending_validation');

        $users = User::query()
            ->with(['profilEtudiant', 'profilDemarcheur', 'profilBoutique', 'documentsIdentite'])
            ->where('role', '!=', 'admin')
            ->when($status !== 'tous', fn($query) => $query->where('statut_verification', $status))
            ->latest()
            ->get();

        return response()->json([
            'data' => $users->map(fn(User $user) => ApiData::user($user))->values(),
        ]);
    }

    public function review(ReviewKycRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($user, $validated) {
            $user->update([
                'statut_verification' => $validated['statut_verification'],
                'commentaire_admin' => $validated['commentaire_admin'] ?? null,
            ]);

            $user->documentsIdentite()->update([
                'statut_verification' => $validated['statut_verification'],
                'commentaire_admin' => $validated['commentaire_admin'] ?? null,
            ]);
        });

        $user->notify(new KycReviewedNotification(
            $validated['statut_verification'],
            $validated['commentaire_admin'] ?? null
        ));

        return response()->json([
            'message' => $validated['statut_verification'] === 'valide'
                ? 'Le dossier KYC a ete approuve.'
                : 'Le dossier KYC a ete rejete avec un commentaire admin.',
            'user' => ApiData::user($user->fresh(['profilEtudiant', 'profilDemarcheur', 'profilBoutique', 'documentsIdentite'])),
        ]);
    }
}