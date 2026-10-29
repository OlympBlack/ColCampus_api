<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use App\Support\ApiData;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\AffinityMatchingService;

class AnnonceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type_annonce' => ['nullable', 'in:CHAMBRE_VIDE,PLACE_LIBRE_HOTE,RECHERCHE_BINOME'],
            'type_chambre' => ['nullable', 'string', 'max:50'],
            'universite' => ['nullable', 'string', 'max:20'],
            'ville' => ['nullable', 'string', 'max:100'],
            'quartier' => ['nullable', 'string', 'max:120'],
            'prix_min' => ['nullable', 'integer', 'min:0'],
            'prix_max' => ['nullable', 'integer', 'min:0'],
        ]);

        $annonces = Annonce::query()
            ->with(['user', 'medias', 'universite'])
            ->where('statut_disponibilite', '!=', 'taken')
            ->where('statut_publication', 'active')
            ->when(isset($validated['type_annonce']), function ($query) use ($validated) {
                $query->where('type_annonce', $validated['type_annonce']);
            })
            ->when(isset($validated['type_chambre']), function ($query) use ($validated) {
                $query->where('type_chambre', $validated['type_chambre']);
            })
            ->when(!empty($validated['universite']), function ($query) use ($validated) {
                $query->whereHas('universite', function ($sub) use ($validated) {
                    $sub->where('code', strtoupper($validated['universite']));
                });
            })
            ->when(!empty($validated['ville']), function ($query) use ($validated) {
                $query->whereHas('universite', function ($sub) use ($validated) {
                    $sub->where('ville', 'like', '%' . $validated['ville'] . '%');
                });
            })
            ->when(isset($validated['quartier']), function ($query) use ($validated) {
                $query->where('quartier', $validated['quartier']);
            })
            ->when(isset($validated['prix_min']), function ($query) use ($validated) {
                $query->where('loyer', '>=', $validated['prix_min']);
            })
            ->when(isset($validated['prix_max']), function ($query) use ($validated) {
                $query->where('loyer', '<=', $validated['prix_max']);
            })
            ->get();

        // Apply Affinity Matching if User is authenticated
        $user = $request->user('sanctum');
        $affinityService = app(AffinityMatchingService::class);
        
        $annonces = $affinityService->rankAnnonces($annonces, $user);

        $favoriIds = $user ? $user->favoris()->pluck('annonce_id')->all() : [];

        return response()->json([
            'data' => $annonces->map(fn (Annonce $annonce) => ApiData::annonce($annonce, [
                'est_favori' => in_array($annonce->id, $favoriIds, true),
            ]))->values(),
        ]);
    }

    public function show(Request $request, Annonce $annonce): JsonResponse
    {
        $annonce->load(['user', 'medias']);

        $annonce->increment('vues_count');

        $user = $request->user('sanctum');
        $isFavori = $user ? $user->favoris()->where('annonce_id', $annonce->id)->exists() : false;

        return response()->json([
            'data' => ApiData::annonce($annonce, [
                'est_favori' => $isFavori,
            ]),
        ]);
    }

    public function similar(Request $request, Annonce $annonce): JsonResponse
    {
        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $annonce->loadMissing(['user', 'medias']);

        $limit = $validated['limit'] ?? 6;

        $similaires = Annonce::query()
            ->with(['user', 'medias'])
            ->whereKeyNot($annonce->id)
            ->where('statut_disponibilite', '!=', 'taken')
            ->where('statut_publication', 'active')
            ->get();

        $data = $this->rankSimilarAnnouncements($annonce, $similaires, $limit)
            ->map(fn (array $item) => ApiData::annonce($item['annonce'], [
                'score_similarite' => $item['score'],
            ]))
            ->values();

        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{annonce: Annonce, score: int}>
     */
    private function rankSimilarAnnouncements(Annonce $reference, EloquentCollection $annonces, int $limit)
    {
        return $annonces
            ->map(function (Annonce $candidate) use ($reference) {
                $score = 0;

                if ($candidate->type_annonce === $reference->type_annonce) {
                    $score += 45;
                }

                if ($candidate->quartier === $reference->quartier) {
                    $score += 30;
                }

                if ($candidate->source_eau === $reference->source_eau) {
                    $score += 10;
                }

                if ($candidate->type_electricite === $reference->type_electricite) {
                    $score += 10;
                }

                $referencePrice = (int) $reference->loyer;
                $candidatePrice = (int) $candidate->loyer;
                $priceGap = abs($candidatePrice - $referencePrice);
                $priceWindow = max($referencePrice * 0.6, 10000);

                if ($priceGap === 0) {
                    $score += 25;
                } elseif ($priceGap < $priceWindow) {
                    $score += (int) round((1 - ($priceGap / $priceWindow)) * 25);
                }

                return [
                    'annonce' => $candidate,
                    'score' => $score,
                ];
            })
            ->sort(function (array $left, array $right) {
                if ($left['score'] === $right['score']) {
                    return $right['annonce']->created_at <=> $left['annonce']->created_at;
                }

                return $right['score'] <=> $left['score'];
            })
            ->take($limit);
    }
}





