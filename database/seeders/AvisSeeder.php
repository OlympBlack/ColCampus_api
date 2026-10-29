<?php

namespace Database\Seeders;

use App\Models\Avis;
use App\Models\User;
use Illuminate\Database\Seeder;

class AvisSeeder extends Seeder
{
    /**
     * Avis de demonstration "je cherche une chambre" pour alimenter la section
     * publique. Idempotent : relancable sans creer de doublons.
     */
    public function run(): void
    {
        $avis = [
            [
                'email' => 'sika.adam@etudiant.uac.bj',
                'quartier' => 'Zogbadjè',
                'type_chambre' => 'CHAMBRE_SALON',
                'nombre_places' => null,
                'budget_min' => 25000,
                'budget_max' => 40000,
                'date_souhaitee' => '2026-11-01',
                'description' => "Je suis etudiante en L3 a la FSA et je cherche une chambre salon proche du campus d'Abomey-Calavi. Je prefere un logement avec eau courante et compteur SBEE individuel. Disponible pour une visite des que possible.",
            ],
            [
                'email' => 'marie.gomez@etudiant.uac.bj',
                'quartier' => 'Coco Gakli',
                'type_chambre' => 'CHAMBRE_SIMPLE',
                'nombre_places' => null,
                'budget_min' => 15000,
                'budget_max' => 25000,
                'date_souhaitee' => '2026-10-30',
                'description' => "Etudiante a l'IFRI, je recherche une chambre simple calme et securisee, de preference dans une concession avec d'autres etudiantes. Budget serre mais je paie regulierement. Merci de me proposer des offres serieuses.",
            ],
            [
                'email' => 'rita.zinsou@etudiant.una.bj',
                'quartier' => 'Ouando',
                'type_chambre' => 'STUDIO',
                'nombre_places' => null,
                'budget_min' => 20000,
                'budget_max' => 35000,
                'date_souhaitee' => '2026-11-15',
                'description' => "Je viens d'integrer l'ISA a Porto-Novo et je cherche un studio meuble ou semi-meuble a Ouando, accessible a pied du campus. Je souhaite un contrat clair et un proprietaire joignable.",
            ],
            [
                'email' => 'koffi.houngbo@etudiant.unstim.bj',
                'quartier' => 'Djègbé',
                'type_chambre' => 'PLACE_COLOCATION',
                'nombre_places' => 1,
                'budget_min' => 12000,
                'budget_max' => 20000,
                'date_souhaitee' => '2026-11-05',
                'description' => "Etudiant a la FAST d'Abomey, je cherche une place en colocation pour partager un deux pieces salon. Je suis calme, non fumeur et je participe aux charges. Ouvert aux visites en fin de semaine.",
            ],
            [
                'email' => 'clarisse.allogbo@etudiant.up.bj',
                'quartier' => 'Zongo',
                'type_chambre' => 'CHAMBRE_SALON',
                'nombre_places' => null,
                'budget_min' => 18000,
                'budget_max' => 30000,
                'date_souhaitee' => '2026-12-01',
                'description' => "Etudiante a l'Universite de Parakou, je recherche une chambre salon propre et proche du campus pour la rentree. J'apprecie les logements avec un espace pour etudier et un acces facile aux transports.",
            ],
            [
                'email' => 'luc.sessou@etudiant.uac.bj',
                'quartier' => 'Akassato',
                'type_chambre' => 'APPARTEMENT',
                'nombre_places' => null,
                'budget_min' => 35000,
                'budget_max' => 50000,
                'date_souhaitee' => '2026-11-20',
                'description' => "Je cherche avec un ami un appartement de deux chambres a Akassato, proche de l'UAC. Nous sommes deux etudiants serieux et nous pouvons fournir des garants. Merci de nous proposer des biens disponibles.",
            ],
        ];

        foreach ($avis as $index => $item) {
            $user = User::where('email', $item['email'])->first();

            if (!$user) {
                continue;
            }

            $universiteId = $user->profilEtudiant?->universite_id;

            $record = Avis::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'quartier' => $item['quartier'],
                ],
                [
                    'universite_id' => $universiteId,
                    'type_chambre' => $item['type_chambre'],
                    'nombre_places' => $item['nombre_places'],
                    'budget_min' => $item['budget_min'],
                    'budget_max' => $item['budget_max'],
                    'date_souhaitee' => $item['date_souhaitee'],
                    'description' => $item['description'],
                    'statut' => 'ouverte',
                ]
            );

            $record->forceFill([
                'created_at' => now()->subHours(($index + 1) * 9),
                'updated_at' => now()->subHours(($index + 1) * 9),
            ])->save();
        }
    }
}
