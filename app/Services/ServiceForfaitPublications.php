<?php

namespace App\Services;

use App\Models\ForfaitPublication;
use App\Models\AchatForfait;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ServiceForfaitPublications
{
    protected KkiapayService $kkiapay;

    // ← Injection via constructor
    public function __construct(KkiapayService $kkiapay)
    {
        $this->kkiapay = $kkiapay;
    }

    public function listerForfaitsActifs()
    {
        return ForfaitPublication::where('est_actif', true)
            ->orderBy('ordre_affichage')
            ->get();
    }

    public function acheterForfait(User $user, ForfaitPublication $forfait, ?string $reference = null, ?string $methode = null)
    {
        return DB::transaction(function () use ($user, $forfait, $reference, $methode) {
            $achat = AchatForfait::create([
                'user_id'                => $user->id,
                'forfait_id'             => $forfait->id,
                'nombre_publications'    => $forfait->nombre_publications,
                'montant'                => $forfait->prix,
                'statut_paiement'        => 'valide',
                'methode_paiement'       => $methode,
                'reference_transaction'  => $reference,
            ]);

            $servicePortefeuille = new ServicePortefeuillePublications();
            $servicePortefeuille->ajouterCreditsAchetes($user, $forfait->nombre_publications, $achat->id);

            return $achat;
        });
    }

    public function validerAchatKkiapay(User $user, ForfaitPublication $forfait, string $transactionId)
    {
        // ← Utilise $this->kkiapay au lieu de new KkiapayService()
        $details = $this->kkiapay->verifyTransaction($transactionId);

        if (!$details || ($details['status'] ?? '') !== 'SUCCESS') {
            throw new \Exception("La transaction Kkiapay n'a pas pu être vérifiée ou a échoué.");
        }

        // Vérification de sécurité critique : s'assurer que le montant payé correspond au prix du forfait
        $montantPaye = isset($details['amount']) ? (int)$details['amount'] : 0;
        if ($montantPaye < $forfait->prix) {
            throw new \Exception("Le montant de la transaction ({$montantPaye}) est inférieur au prix du forfait ({$forfait->prix}).");
        }

        return $this->acheterForfait($user, $forfait, $transactionId, 'kkiapay');
    }
}