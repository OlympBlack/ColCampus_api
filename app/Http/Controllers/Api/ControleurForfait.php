<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ServiceForfaitPublications;
use App\Models\ForfaitPublication;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ControleurForfait extends Controller
{
    protected $serviceForfait;

    public function __construct(ServiceForfaitPublications $serviceForfait)
    {
        $this->serviceForfait = $serviceForfait;
    }

    /**
     * Voir tous les forfaits disponibles
     */
    public function index(): JsonResponse
    {
        $forfaits = $this->serviceForfait->listerForfaitsActifs();
        return response()->json([
            'data' => $forfaits
        ]);
    }

    /**
     * Acheter un forfait : le paiement Kkiapay est obligatoire (aucun achat gratuit).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'forfait_id' => 'required|exists:forfaits_publications,id',
            'transaction_id' => 'required|string',
            'methode' => 'required|string|in:kkiapay',
        ]);

        $forfait = ForfaitPublication::findOrFail($request->forfait_id);
        $user = $request->user();

        try {
            $achat = $this->serviceForfait->validerAchatKkiapay($user, $forfait, $request->transaction_id);

            return response()->json([
                'message' => 'Forfait pack activé avec succès.',
                'achat' => $achat
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }
}

