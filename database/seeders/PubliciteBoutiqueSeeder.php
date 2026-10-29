<?php

namespace Database\Seeders;

use App\Models\PubliciteBoutique;
use App\Models\User;
use Illuminate\Database\Seeder;

class PubliciteBoutiqueSeeder extends Seeder
{
    public function run(): void
    {
        PubliciteBoutique::query()->delete();

        $alice = User::where('email', 'alice.meubles@shop.bj')->firstOrFail();
        $saliou = User::where('email', 'saliou.info@shop.bj')->firstOrFail();

        $publicites = [
            [
                'user_id' => $alice->id,
                'titre_promo' => 'Alice Meubles equipe votre chambre etudiante en 24h',
                'url_image_banniere' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1400&q=80',
                'lien_action' => '#/inscription',
                'quartier_cible' => 'Zogbadje',
            ],
            [
                'user_id' => $saliou->id,
                'titre_promo' => 'Ordinateurs, routeurs et accessoires pour votre installation a Calavi',
                'url_image_banniere' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1400&q=80',
                'lien_action' => '#/inscription',
                'quartier_cible' => 'Tankpe',
            ],
            [
                'user_id' => $alice->id,
                'titre_promo' => 'Pack lit mousse, table et rangement special nouvelle location',
                'url_image_banniere' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1400&q=80',
                'lien_action' => '#/boutiques',
                'quartier_cible' => null,
            ],
            [
                'user_id' => $saliou->id,
                'titre_promo' => 'Box internet, multiprises et petits accessoires pour chambres et colocations',
                'url_image_banniere' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1400&q=80',
                'lien_action' => '#/boutiques',
                'quartier_cible' => 'Bidossessi',
            ],
            [
                'user_id' => $alice->id,
                'titre_promo' => 'Promo rentree : bureau, chaise et etagere pour espace d etude',
                'url_image_banniere' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1400&q=80',
                'lien_action' => '#/boutiques',
                'quartier_cible' => 'Houeto',
            ],
            [
                'user_id' => $saliou->id,
                'titre_promo' => 'Publications boutique : imprimante, onduleur et connectique disponibles a prix etudiant',
                'url_image_banniere' => 'https://images.unsplash.com/photo-1496171367470-9ed9a91ea931?auto=format&fit=crop&w=1400&q=80',
                'lien_action' => '#/boutiques',
                'quartier_cible' => null,
            ],
            [
                'user_id' => $alice->id,
                'titre_promo' => 'Meubles et literie livres a Oganla et Adjina (Porto-Novo)',
                'url_image_banniere' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1400&q=80',
                'lien_action' => '#/boutiques',
                'quartier_cible' => 'Oganla Nord',
            ],
            [
                'user_id' => $saliou->id,
                'titre_promo' => 'Accessoires informatiques pour etudiants de Parakou (Banikanni, Albarika)',
                'url_image_banniere' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1400&q=80',
                'lien_action' => '#/boutiques',
                'quartier_cible' => 'Banikanni',
            ],
        ];

        foreach ($publicites as $publicite) {
            PubliciteBoutique::create($publicite);
        }
    }
}