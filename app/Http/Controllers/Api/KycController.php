<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreKycDocumentsRequest;
use App\Support\ApiData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class KycController extends Controller
{
    public function store(StoreKycDocumentsRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        DB::transaction(function () use ($user, $validated) {
            $user->documentsIdentite()->delete();

            foreach ($validated['documents'] as $document) {
                $path = $document['fichier']->store('kyc-documents', 'public');

                $user->documentsIdentite()->create([
                    'type_piece' => $document['type_piece'],
                    'numero_piece' => $document['numero_piece'],
                    'photo_piece_url' => $path,
                    'statut_verification' => 'en_attente',
                    'commentaire_admin' => null,
                ]);
            }

            $user->update([
                'statut_verification' => 'pending_validation',
                'commentaire_admin' => null,
            ]);
        });

        return response()->json([
            'message' => 'Vos documents ont ete transmis a l administration. Le statut passe en attente de verification.',
            'user' => ApiData::user($user->fresh(['profilEtudiant', 'profilDemarcheur', 'profilBoutique', 'documentsIdentite'])),
        ]);
    }
}
