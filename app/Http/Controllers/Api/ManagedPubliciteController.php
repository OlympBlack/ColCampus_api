<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePubliciteBoutiqueRequest;
use App\Support\ApiData;
use Illuminate\Http\JsonResponse;

class ManagedPubliciteController extends Controller
{
    public function store(StorePubliciteBoutiqueRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'boutique') {
            return response()->json(['message' => 'Seules les boutiques peuvent publier une banniere.'], 403);
        }

        $validated = $request->validated();

        $publicite = $user->publicitesBoutiques()->create([
            'titre_promo' => $validated['titre_promo'],
            'url_image_banniere' => $request->file('banniere')->store('publicites', 'public'),
            'lien_action' => $validated['lien_action'],
            'quartier_cible' => $validated['quartier_cible'] ?? null,
            'vues_count' => 0,
            'clics_count' => 0,
        ]);

        return response()->json([
            'message' => 'Banniere publicitaire creee avec succes.',
            'data' => ApiData::publicite($publicite->fresh(['user.profilBoutique'])),
        ], 201);
    }
}
