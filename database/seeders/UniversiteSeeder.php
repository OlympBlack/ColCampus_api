<?php

namespace Database\Seeders;

use App\Models\Quartier;
use App\Models\Universite;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class UniversiteSeeder extends Seeder
{
    /**
     * Anciennes valeurs d'énumération Calavi → nomenclature officielle.
     */
    private const REMAPPAGE = [
        'Zogbadje' => 'Zogbadje',
        'Tankpe' => 'Tankpe',
        'Togoudo' => 'Godomey-Togoudo',
        'Bidossessi' => 'Bidossessi',
        'Arconville' => 'Arconville',
        'Houeto' => 'Houeto',
        'Ouedo' => 'Ouedo Centre',
        'Glo-Djigbe' => 'Golo-Djigbe',
        'Hevie' => 'Hevie Centre',
        'Kpota' => 'Djekpota',
        'Acacia' => 'Acacia',
        'Calavi-Bas' => 'Calavi-Bas',
    ];

    public function run(): void
    {
        Artisan::call('quartiers:import');

        $uac = Universite::where('code', 'UAC')->first();
        if (!$uac) {
            return;
        }

        foreach (self::REMAPPAGE as $ancien => $nouveau) {
            $quartier = Quartier::where('universite_id', $uac->id)->where('nom', $nouveau)->first();
            if ($quartier) {
                DB::table('annonces')->where('quartier', $ancien)->update(['quartier' => $nouveau]);
            }
        }

        DB::table('annonces')->whereNull('universite_id')->update(['universite_id' => $uac->id]);

        DB::table('profils_etudiants')
            ->join('users', 'users.id', '=', 'profils_etudiants.user_id')
            ->whereNull('profils_etudiants.universite_id')
            ->where(function ($query) {
                $query->where('users.email', 'like', '%@etudiant.ifri.bj')
                    ->orWhere('users.email', 'like', '%@etudiant.uac.bj');
            })
            ->update(['profils_etudiants.universite_id' => $uac->id]);

        foreach (['una' => 'UNA', 'unstim' => 'UNSTIM', 'up' => 'UP'] as $domaine => $code) {
            $universite = Universite::where('code', $code)->first();
            if (!$universite) {
                continue;
            }

            DB::table('profils_etudiants')
                ->join('users', 'users.id', '=', 'profils_etudiants.user_id')
                ->whereNull('profils_etudiants.universite_id')
                ->where('users.email', 'like', '%@etudiant.'.$domaine.'.bj')
                ->update(['profils_etudiants.universite_id' => $universite->id]);
        }
    }
}
