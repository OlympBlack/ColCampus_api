<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use App\Models\CollectionImmobiliere;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollectionImmobiliereController extends Controller
{
    /**
     * List all active collections with real dynamic property counts.
     */
    public function index(): JsonResponse
    {
        $collections = CollectionImmobiliere::query()
            ->active()
            ->ordered()
            ->get()
            ->map(function (CollectionImmobiliere $collection) {
                return $this->formatCollection($collection);
            });

        return response()->json([
            'data' => $collections,
        ]);
    }

    /**
     * Show a single collection by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $collection = CollectionImmobiliere::query()
            ->where('slug', $slug)
            ->first();

        if (!$collection) {
            return response()->json(['message' => 'Collection introuvable'], 404);
        }

        return response()->json([
            'data' => $this->formatCollection($collection),
        ]);
    }

    /**
     * Compute properties count and format collection attributes.
     */
    private function formatCollection(CollectionImmobiliere $collection): array
    {
        $count = 0;
        if (!empty($collection->room_type_value)) {
            $count = Annonce::query()
                ->where('statut_disponibilite', '!=', 'taken')
                ->where(function ($q) use ($collection) {
                    $q->where('type_chambre', $collection->room_type_value)
                      ->orWhere('type_chambre', $collection->title);
                })
                ->count();
        }

        if ($count === 0 && !empty($collection->title)) {
            $count = Annonce::query()
                ->where('statut_disponibilite', '!=', 'taken')
                ->where('titre', 'like', '%' . $collection->title . '%')
                ->count();
        }

        $propertiesCount = $count > 0 ? $count : (int) $collection->fallback_count;

        return [
            'id' => $collection->id,
            'slug' => $collection->slug,
            'title' => $collection->title,
            'room_type_value' => $collection->room_type_value,
            'price_range' => $collection->price_range,
            'fallback_count' => (int) $collection->fallback_count,
            'properties_count' => $propertiesCount,
            'image' => $collection->image_url ?? $collection->image,
            'hero_banner' => $collection->hero_banner_url ?? $collection->hero_banner,
            'description' => $collection->description,
            'hero_description' => $collection->hero_description,
            'features' => $collection->features ?? [],
            'ideal_for' => $collection->ideal_for,
            'avg_rent' => $collection->avg_rent,
            'ordre' => (int) $collection->ordre,
            'is_active' => (bool) $collection->is_active,
            'created_at' => $collection->created_at?->toISOString(),
            'updated_at' => $collection->updated_at?->toISOString(),
        ];
    }
}
