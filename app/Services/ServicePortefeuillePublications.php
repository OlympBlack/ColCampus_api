<?php

namespace App\Services;

use App\Models\User;
use App\Models\PortefeuillePublication;
use App\Models\TransactionCredit;
use Illuminate\Support\Facades\DB;

class ServicePortefeuillePublications
{
    /**
     * Initialiser un portefeuille pour un nouvel utilisateur (démarcheur)
     */
    public function initialiserPortefeuille(User $user)
    {
        return DB::transaction(function () use ($user) {
            $portefeuille = PortefeuillePublication::create([
                'user_id' => $user->id,
                'credits_gratuits' => 2,
                'credits_achetes' => 0,
                'credits_utilises' => 0,
            ]);

            // Enregistrer la transaction initiale
            TransactionCredit::create([
                'user_id' => $user->id,
                'type' => 'GRATUIT',
                'montant' => 2,
                'description' => 'Crédits de bienvenue à l\'inscription',
            ]);

            return $portefeuille;
        });
    }

    /**
     * Vérifier si l'utilisateur a des crédits disponibles
     */
    public function aDesCreditsDisponibles(User $user): bool
    {
        $portefeuille = $user->portefeuillePublication;
        if (!$portefeuille) {
            return false;
        }

        return $portefeuille->credits_restants > 0;
    }

    /**
     * Utiliser un crédit pour une publication
     */
    public function utiliserCredit(User $user, $annonceId)
    {
        return DB::transaction(function () use ($user, $annonceId) {
            $portefeuille = $user->portefeuillePublication;

            if (!$portefeuille || $portefeuille->credits_restants <= 0) {
                throw new \Exception("Crédits insuffisants pour publier cette annonce.");
            }

            $portefeuille->increment('credits_utilises');

            TransactionCredit::create([
                'user_id' => $user->id,
                'type' => 'UTILISATION',
                'montant' => -1,
                'source_type' => 'Annonce',
                'source_id' => $annonceId,
                'description' => 'Publication d\'une nouvelle annonce',
            ]);

            return true;
        });
    }

    /**
     * Ajouter des crédits achetés
     */
    public function ajouterCreditsAchetes(User $user, int $nombre, $achatId)
    {
        return DB::transaction(function () use ($user, $nombre, $achatId) {
            $portefeuille = $user->portefeuillePublication;

            if (!$portefeuille) {
                // Au cas où, mais normalement initialisé à l'inscription
                $portefeuille = $this->initialiserPortefeuille($user);
            }

            $portefeuille->increment('credits_achetes', $nombre);

            TransactionCredit::create([
                'user_id' => $user->id,
                'type' => 'ACHAT',
                'montant' => $nombre,
                'source_type' => 'AchatForfait',
                'source_id' => $achatId,
                'description' => "Achat d'un pack de {$nombre} publications",
            ]);

            return true;
        });
    }
}
