<?php

namespace App\Services;

use App\Models\User;
use App\Models\Annonce;
use Illuminate\Support\Facades\DB;

class ServicePublicationAnnonce
{
    protected $servicePortefeuille;

    public function __construct(ServicePortefeuillePublications $servicePortefeuille)
    {
        $this->servicePortefeuille = $servicePortefeuille;
    }

    /**
     * Tenter de publier une annonce avec gestion des crédits
     */
    public function publierAvecCredits(User $user, array $donneesAnnonce)
    {
        if ($user->role !== 'demarcheur') {
            // Logique standard pour les autres rôles (si applicable)
            return Annonce::create(array_merge($donneesAnnonce, [
                'user_id' => $user->id,
                'statut_publication' => 'active',
            ]));
        }

        return DB::transaction(function () use ($user, $donneesAnnonce) {
            // 1. Vérifier crédits
            if (!$this->servicePortefeuille->aDesCreditsDisponibles($user)) {
                throw new \Exception("Quota de publication épuisé. Veuillez acheter des crédits.");
            }

            // 2. Créer l'annonce
            $annonce = Annonce::create(array_merge($donneesAnnonce, [
                'user_id' => $user->id,
                'statut_publication' => 'active',
            ]));

            // 3. Déduire crédit
            $this->servicePortefeuille->utiliserCredit($user, $annonce->id);

            return $annonce;
        });
    }
}
