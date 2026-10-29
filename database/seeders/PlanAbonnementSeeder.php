<?php

namespace Database\Seeders;

use App\Models\Abonnement;
use App\Models\PlanAbonnement;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlanAbonnementSeeder extends Seeder
{
    public function run(): void
    {
        $packPro = PlanAbonnement::updateOrCreate(
            ['nom_plan' => 'Pack Pro'],
            [
                'prix' => 10000,
                'duree_mois' => 1,
                'limite_annonces' => 10,
            ]
        );

        $demarcheur = User::where('email', 'marc.agossou@immo.bj')->first();

        if ($demarcheur) {
            Abonnement::updateOrCreate(
                ['user_id' => $demarcheur->id, 'plan_id' => $packPro->id],
                [
                    'date_debut' => now()->subDays(5),
                    'date_fin' => now()->addDays(25),
                    'statut_paiement' => 'paid',
                ]
            );
        }
    }
}
