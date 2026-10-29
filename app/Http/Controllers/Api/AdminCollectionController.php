<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use App\Models\CollectionImmobiliere;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminCollectionController extends Controller
{
    /**
     * List all collections for Admin (including inactive).
     */
    public function index(Request $request): JsonResponse
    {
        $query = CollectionImmobiliere::query()->ordered();

        if ($request->filled('q')) {
            $search = $request->query('q');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('room_type_value', 'like', "%{$search}%");
            });
        }

        $collections = $query->get()->map(function (CollectionImmobiliere $collection) {
            return $this->formatCollection($collection);
        });

        return response()->json([
            'data' => $collections,
        ]);
    }

    /**
     * Store a new collection.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', 'unique:collections_immobilieres,slug'],
            'room_type_value' => ['nullable', 'string', 'max:100'],
            'price_range' => ['nullable', 'string', 'max:100'],
            'fallback_count' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable'],
            'hero_banner' => ['nullable'],
            'description' => ['nullable', 'string'],
            'hero_description' => ['nullable', 'string'],
            'features' => ['nullable'],
            'ideal_for' => ['nullable', 'string', 'max:150'],
            'avg_rent' => ['nullable', 'string', 'max:100'],
            'ordre' => ['nullable', 'integer'],
            'is_active' => ['nullable'],
        ]);

        $slug = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['title']);

        // Check uniqueness and append if needed
        $originalSlug = $slug;
        $counter = 1;
        while (CollectionImmobiliere::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('collections', 'public');
        } elseif (is_string($request->input('image')) && !empty($request->input('image'))) {
            $imagePath = $request->input('image');
        }

        $heroBannerPath = null;
        if ($request->hasFile('hero_banner')) {
            $heroBannerPath = $request->file('hero_banner')->store('collections', 'public');
        } elseif (is_string($request->input('hero_banner')) && !empty($request->input('hero_banner'))) {
            $heroBannerPath = $request->input('hero_banner');
        }

        $features = $this->parseFeatures($request->input('features'));
        $isActive = $request->has('is_active') ? filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN) : true;

        $collection = CollectionImmobiliere::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'room_type_value' => $validated['room_type_value'] ?? null,
            'price_range' => $validated['price_range'] ?? null,
            'fallback_count' => $validated['fallback_count'] ?? 10,
            'image' => $imagePath,
            'hero_banner' => $heroBannerPath,
            'description' => $validated['description'] ?? null,
            'hero_description' => $validated['hero_description'] ?? null,
            'features' => $features,
            'ideal_for' => $validated['ideal_for'] ?? null,
            'avg_rent' => $validated['avg_rent'] ?? null,
            'ordre' => $validated['ordre'] ?? (CollectionImmobiliere::max('ordre') + 1),
            'is_active' => $isActive,
        ]);

        return response()->json([
            'message' => 'Collection créée avec succès',
            'data' => $this->formatCollection($collection),
        ], 201);
    }

    /**
     * Show a collection.
     */
    public function show(int $id): JsonResponse
    {
        $collection = CollectionImmobiliere::findOrFail($id);
        return response()->json([
            'data' => $this->formatCollection($collection),
        ]);
    }

    /**
     * Update a collection.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $collection = CollectionImmobiliere::findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', Rule::unique('collections_immobilieres', 'slug')->ignore($collection->id)],
            'room_type_value' => ['nullable', 'string', 'max:100'],
            'price_range' => ['nullable', 'string', 'max:100'],
            'fallback_count' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable'],
            'hero_banner' => ['nullable'],
            'description' => ['nullable', 'string'],
            'hero_description' => ['nullable', 'string'],
            'features' => ['nullable'],
            'ideal_for' => ['nullable', 'string', 'max:150'],
            'avg_rent' => ['nullable', 'string', 'max:100'],
            'ordre' => ['nullable', 'integer'],
            'is_active' => ['nullable'],
        ]);

        $slug = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['title']);

        $data = [
            'title' => $validated['title'],
            'slug' => $slug,
            'room_type_value' => $validated['room_type_value'] ?? null,
            'price_range' => $validated['price_range'] ?? null,
            'fallback_count' => $validated['fallback_count'] ?? $collection->fallback_count,
            'description' => $validated['description'] ?? null,
            'hero_description' => $validated['hero_description'] ?? null,
            'ideal_for' => $validated['ideal_for'] ?? null,
            'avg_rent' => $validated['avg_rent'] ?? null,
        ];

        if ($request->has('ordre')) {
            $data['ordre'] = (int) $request->input('ordre');
        }

        if ($request->has('is_active')) {
            $data['is_active'] = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);
        }

        if ($request->has('features')) {
            $data['features'] = $this->parseFeatures($request->input('features'));
        }

        if ($request->hasFile('image')) {
            if ($collection->image && !str_starts_with($collection->image, 'http')) {
                Storage::disk('public')->delete($collection->image);
            }
            $data['image'] = $request->file('image')->store('collections', 'public');
        } elseif ($request->filled('image_url_text')) {
            $data['image'] = $request->input('image_url_text');
        } elseif (is_string($request->input('image')) && !empty($request->input('image')) && str_starts_with($request->input('image'), 'http')) {
            $data['image'] = $request->input('image');
        }

        if ($request->hasFile('hero_banner')) {
            if ($collection->hero_banner && !str_starts_with($collection->hero_banner, 'http')) {
                Storage::disk('public')->delete($collection->hero_banner);
            }
            $data['hero_banner'] = $request->file('hero_banner')->store('collections', 'public');
        } elseif ($request->filled('hero_banner_url_text')) {
            $data['hero_banner'] = $request->input('hero_banner_url_text');
        } elseif (is_string($request->input('hero_banner')) && !empty($request->input('hero_banner')) && str_starts_with($request->input('hero_banner'), 'http')) {
            $data['hero_banner'] = $request->input('hero_banner');
        }

        $collection->update($data);

        return response()->json([
            'message' => 'Collection mise à jour avec succès',
            'data' => $this->formatCollection($collection->fresh()),
        ]);
    }

    /**
     * Delete a collection.
     */
    public function destroy(int $id): JsonResponse
    {
        $collection = CollectionImmobiliere::findOrFail($id);

        if ($collection->image && !str_starts_with($collection->image, 'http')) {
            Storage::disk('public')->delete($collection->image);
        }
        if ($collection->hero_banner && !str_starts_with($collection->hero_banner, 'http')) {
            Storage::disk('public')->delete($collection->hero_banner);
        }

        $collection->delete();

        return response()->json([
            'message' => 'Collection supprimée avec succès',
        ]);
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $collection = CollectionImmobiliere::findOrFail($id);
        $collection->is_active = !$collection->is_active;
        $collection->save();

        return response()->json([
            'message' => $collection->is_active ? 'Collection activée' : 'Collection désactivée',
            'data' => $this->formatCollection($collection),
        ]);
    }

    /**
     * Parse features input into string array.
     */
    private function parseFeatures(mixed $raw): array
    {
        if (is_array($raw)) {
            return array_values(array_filter(array_map('trim', $raw)));
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return array_values(array_filter(array_map('trim', $decoded)));
            }

            return array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $raw))));
        }

        return [];
    }

    /**
     * Format collection.
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

        return [
            'id' => $collection->id,
            'slug' => $collection->slug,
            'title' => $collection->title,
            'room_type_value' => $collection->room_type_value,
            'price_range' => $collection->price_range,
            'fallback_count' => (int) $collection->fallback_count,
            'properties_count' => $count > 0 ? $count : (int) $collection->fallback_count,
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
