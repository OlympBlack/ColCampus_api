<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransactionCredit;
use App\Models\AchatForfait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ControleurPortefeuille extends Controller
{
    /**
     * Voir l'état du portefeuille et l'historique
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $portefeuille = $user->portefeuillePublication;

        if (!$portefeuille && $user->role === 'demarcheur') {
            // Initialisation de secours si non existant
            $service = new \App\Services\ServicePortefeuillePublications();
            $portefeuille = $service->initialiserPortefeuille($user);
        }

        $transactions = TransactionCredit::where('user_id', $user->id)
            ->latest()
            ->take(20)
            ->get();

        $achats = AchatForfait::with('forfait')
            ->where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'portefeuille' => [
                'credits_restants' => $portefeuille ? $portefeuille->credits_restants : 0,
                'credits_gratuits' => $portefeuille ? $portefeuille->credits_gratuits : 0,
                'credits_achetes' => $portefeuille ? $portefeuille->credits_achetes : 0,
                'credits_utilises' => $portefeuille ? $portefeuille->credits_utilises : 0,
            ],
            'transactions' => $transactions,
            'achats' => $achats,
        ]);
    }
}
