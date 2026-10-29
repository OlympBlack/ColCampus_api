<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Annonce;
use App\Models\Signalement;
use App\Support\ApiData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SignalementController extends Controller
{
    /**
     * Dépôt public ou authentifié d'un signalement d'annonce
     */
    public function store(Request $request, Annonce $annonce): JsonResponse
    {
        $validated = $request->validate([
            'motif' => 'required|string|in:ARNAQUE_AVANCE,LOGEMENT_INDISPONIBLE,PRIX_ABUSIF,FAUSSES_INFOS,COMPORTEMENT_INAPPROPRIE,AUTRE',
            'description' => 'required|string|min:10|max:3000',
            'nom' => 'nullable|string|max:120',
            'email' => 'nullable|email|max:150',
            'telephone' => 'nullable|string|max:40',
        ], [
            'motif.required' => 'Veuillez sélectionner un motif de signalement.',
            'motif.in' => 'Le motif sélectionné est invalide.',
            'description.required' => 'Veuillez préciser la raison de votre signalement.',
            'description.min' => 'La description doit contenir au moins 10 caractères.',
        ]);

        $user = $request->user('sanctum');

        $signalement = Signalement::create([
            'annonce_id' => $annonce->id,
            'user_id' => $user?->id,
            'nom' => $validated['nom'] ?? ($user ? ($user->prenoms . ' ' . $user->name) : null),
            'email' => $validated['email'] ?? $user?->email,
            'telephone' => $validated['telephone'] ?? $user?->telephone,
            'motif' => $validated['motif'],
            'description' => $validated['description'],
            'statut' => 'EN_ATTENTE',
        ]);

        return response()->json([
            'message' => 'Votre signalement a bien été enregistré. Notre équipe de modération l’examinera dans les plus brefs délais.',
            'data' => $signalement,
        ], 201);
    }

    /**
     * Liste des signalements pour l'espace administration
     */
    public function index(Request $request): JsonResponse
    {
        $statut = $request->query('statut');
        $motif = $request->query('motif');
        $search = $request->query('q');

        $query = Signalement::query()
            ->with(['annonce.user', 'annonce.medias', 'user', 'traitePar'])
            ->latest();

        if ($statut && $statut !== 'all') {
            $query->where('statut', $statut);
        }

        if ($motif && $motif !== 'all') {
            $query->where('motif', $motif);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('nom', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telephone', 'like', "%{$search}%")
                    ->orWhereHas('annonce', function ($qa) use ($search) {
                        $qa->where('titre', 'like', "%{$search}%")
                            ->orWhere('quartier', 'like', "%{$search}%");
                    });
            });
        }

        $signalements = $query->paginate(25);

        $counts = [
            'total' => Signalement::count(),
            'en_attente' => Signalement::where('statut', 'EN_ATTENTE')->count(),
            'traite' => Signalement::where('statut', 'TRAITE')->count(),
            'rejete' => Signalement::where('statut', 'REJETE')->count(),
        ];

        return response()->json([
            'data' => collect($signalements->items())->map(function (Signalement $s) {
                return [
                    'id' => $s->id,
                    'annonce_id' => $s->annonce_id,
                    'motif' => $s->motif,
                    'description' => $s->description,
                    'statut' => $s->statut,
                    'decision' => $s->decision,
                    'notes_admin' => $s->notes_admin,
                    'created_at' => $s->created_at->toISOString(),
                    'traite_le' => $s->traite_le?->toISOString(),
                    'signaleur' => [
                        'user_id' => $s->user_id,
                        'nom' => $s->nom ?: ($s->user ? ($s->user->prenoms . ' ' . $s->user->name) : 'Anonyme'),
                        'email' => $s->email ?: $s->user?->email,
                        'telephone' => $s->telephone ?: $s->user?->telephone,
                        'is_registered' => (bool) $s->user_id,
                    ],
                    'annonce' => $s->annonce ? [
                        'id' => $s->annonce->id,
                        'titre' => $s->annonce->titre,
                        'type_annonce' => $s->annonce->type_annonce,
                        'loyer' => (int) $s->annonce->loyer,
                        'quartier' => $s->annonce->quartier,
                        'statut_disponibilite' => $s->annonce->statut_disponibilite,
                        'image' => $s->annonce->medias->first()?->url ?? '/placeholder-house.jpg',
                        'auteur' => [
                            'id' => $s->annonce->user?->id,
                            'nom' => $s->annonce->user ? ($s->annonce->user->prenoms . ' ' . $s->annonce->user->name) : 'Auteur inconnu',
                            'role' => $s->annonce->user?->role,
                            'email' => $s->annonce->user?->email,
                            'telephone' => $s->annonce->user?->telephone,
                        ],
                    ] : null,
                    'traite_par' => $s->traitePar ? [
                        'id' => $s->traitePar->id,
                        'nom' => $s->traitePar->prenoms . ' ' . $s->traitePar->name,
                    ] : null,
                ];
            })->values(),
            'counts' => $counts,
            'pagination' => [
                'total' => $signalements->total(),
                'current_page' => $signalements->currentPage(),
                'last_page' => $signalements->lastPage(),
            ],
        ]);
    }

    /**
     * Traiter ou mettre à jour un signalement
     */
    public function update(Request $request, $id): JsonResponse
    {
        $signalement = Signalement::with('annonce')->findOrFail($id);

        $validated = $request->validate([
            'statut' => 'required|string|in:EN_ATTENTE,TRAITE,REJETE',
            'decision' => 'nullable|string|max:100',
            'notes_admin' => 'nullable|string|max:2000',
            'action_annonce' => 'nullable|string|in:aucune,desactiver,supprimer',
        ]);

        $signalement->update([
            'statut' => $validated['statut'],
            'decision' => $validated['decision'] ?? null,
            'notes_admin' => $validated['notes_admin'] ?? null,
            'traite_par_user_id' => $request->user()->id,
            'traite_le' => now(),
        ]);

        // Action immédiate sur l'annonce si demandée
        if (!empty($validated['action_annonce']) && $signalement->annonce) {
            if ($validated['action_annonce'] === 'desactiver') {
                $signalement->annonce->update(['statut_disponibilite' => 'taken']);
            } elseif ($validated['action_annonce'] === 'supprimer') {
                $signalement->annonce->delete();
            }
        }

        return response()->json([
            'message' => 'Le signalement a été mis à jour avec succès.',
            'data' => $signalement->fresh(['annonce.user', 'user', 'traitePar']),
        ]);
    }

    /**
     * Supprimer un signalement
     */
    public function destroy($id): JsonResponse
    {
        $signalement = Signalement::findOrFail($id);
        $signalement->delete();

        return response()->json([
            'message' => 'Signalement supprimé avec succès.',
        ]);
    }
}
