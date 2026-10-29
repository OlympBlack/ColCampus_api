<?php

namespace Database\Seeders;

use App\Models\PortefeuillePublication;
use App\Models\User;
use App\Services\ServicePortefeuillePublications;
use Illuminate\Database\Seeder;

class PortefeuillePublicationSeeder extends Seeder
{
    /**
     * Initialise le portefeuille de credits des demarcheurs
     * (les comptes crees par ProfilesSeeder n'en beneficient pas via l'inscription).
     */
    public function run(): void
    {
        $service = app(ServicePortefeuillePublications::class);

        User::where('role', 'demarcheur')->each(function (User $user) use ($service) {
            $exists = PortefeuillePublication::where('user_id', $user->id)->exists();

            if (! $exists) {
                $service->initialiserPortefeuille($user);
            }
        });
    }
}
