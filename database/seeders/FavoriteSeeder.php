<?php

namespace Database\Seeders;

use App\Models\Annonce;
use App\Models\Favori;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'jean.tossou@etudiant.uac.bj')->first();

        if (!$user) {
            return;
        }

        $annonces = Annonce::query()->where('user_id', '!=', $user->id)->take(3)->get();

        foreach ($annonces as $annonce) {
            Favori::firstOrCreate([
                'user_id' => $user->id,
                'annonce_id' => $annonce->id,
            ]);
        }
    }
}
