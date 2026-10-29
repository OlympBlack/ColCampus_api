<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function store(Request $request, Annonce $annonce): JsonResponse
    {
        $request->user()->favoris()->firstOrCreate([
            'annonce_id' => $annonce->id,
        ]);

        return response()->json([
            'message' => 'Annonce ajoutée aux favoris.',
            'est_favori' => true,
        ], 201);
    }

    public function destroy(Request $request, Annonce $annonce): JsonResponse
    {
        $request->user()->favoris()->where('annonce_id', $annonce->id)->delete();

        return response()->json([
            'message' => 'Annonce retirée des favoris.',
            'est_favori' => false,
        ]);
    }
}
