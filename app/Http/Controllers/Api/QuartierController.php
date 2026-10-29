<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quartier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuartierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'universite' => ['nullable', 'string', 'max:20'],
            'universite_id' => ['nullable', 'integer', 'exists:universites,id'],
        ]);

        $quartiers = Quartier::query()
            ->with('universite:id,code,nom,ville')
            ->when(!empty($validated['universite_id']), function ($query) use ($validated) {
                $query->where('universite_id', $validated['universite_id']);
            })
            ->when(!empty($validated['universite']) && empty($validated['universite_id']), function ($query) use ($validated) {
                $query->whereHas('universite', function ($sub) use ($validated) {
                    $sub->where('code', strtoupper($validated['universite']));
                });
            })
            ->orderBy('nom')
            ->get();

        return response()->json([
            'data' => $quartiers->map(fn (Quartier $quartier) => [
                'id' => $quartier->id,
                'nom' => $quartier->nom,
                'arrondissement' => $quartier->arrondissement,
                'source' => $quartier->source,
                'universite' => $quartier->universite?->code,
            ])->values(),
        ]);
    }
}
