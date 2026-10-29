<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UniversiteSeeder::class,
            ProfilesSeeder::class,
            PlanAbonnementSeeder::class,
            PortefeuillePublicationSeeder::class,
            ForfaitPublicationSeeder::class,
            AnnonceSeeder::class,
            AvisSeeder::class,
            PubliciteBoutiqueSeeder::class,
            FavoriteSeeder::class,
            CollectionImmobiliereSeeder::class,
        ]);
    }
}
