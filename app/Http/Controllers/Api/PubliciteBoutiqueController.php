<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PubliciteBoutique;
use App\Support\ApiData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PubliciteBoutiqueController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quartier' => ['nullable', 'string', 'max:255'],
        ]);

        $publicites = PubliciteBoutique::query()
            ->with(['user.profilBoutique'])
            ->when(isset($validated['quartier']), function ($query) use ($validated) {
                $query->where(function ($innerQuery) use ($validated) {
                    $innerQuery
                        ->whereNull('quartier_cible')
                        ->orWhere('quartier_cible', $validated['quartier']);
                });
            })
            ->latest()
            ->get();

        if ($publicites->isNotEmpty()) {
            PubliciteBoutique::query()
                ->whereIn('id', $publicites->pluck('id'))
                ->increment('vues_count');

            $publicites->each(function (PubliciteBoutique $publicite) {
                $publicite->vues_count++;
            });
        }

        return response()->json([
            'data' => $publicites->map(fn (PubliciteBoutique $publicite) => ApiData::publicite($publicite))->values(),
        ]);
    }

    public function click(PubliciteBoutique $publicite): JsonResponse
    {
        $publicite->increment('clics_count');

        return response()->json([
            'message' => 'Clic publicitaire enregistre.',
            'data' => ApiData::publicite($publicite->fresh(['user.profilBoutique'])),
        ]);
    }
}
