<?php

namespace App\Console\Commands;

use App\Models\Quartier;
use App\Models\Universite;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportQuartiers extends Command
{
    protected $signature = 'quartiers:import
        {--fetch : Re-télécharger la nomenclature depuis l API de découpage du Bénin}
        {--strict : Échouer si un dépôt distant est injoignable}';

    protected $description = 'Importe les universités et leurs quartiers (API de découpage ou dump local)';

    private const DEPARTEMENTS = [
        'UAC' => 3,
        'UNA' => 10,
        'UNSTIM' => 12,
        'UP' => 4,
    ];

    private const COMMUNES = [
        'UAC' => 'ABOMEY-CALAVI',
        'UNA' => 'PORTO-NOVO',
        'UNSTIM' => 'ABOMEY',
        'UP' => 'PARAKOU',
    ];

    public function handle(): int
    {
        $donnees = $this->option('fetch')
            ? $this->telecharger()
            : $this->chargerLocal();

        if ($donnees === null) {
            $this->error('Impossible de charger la nomenclature des quartiers.');

            return self::FAILURE;
        }

        $universites = [];
        foreach ($donnees['universites'] as $def) {
            $universites[$def['code']] = Universite::updateOrCreate(
                ['code' => $def['code']],
                [
                    'nom' => $def['nom'],
                    'ville' => $def['ville'],
                    'departement' => $def['departement'],
                    'commune' => $def['commune'],
                    'accent' => (bool) ($def['accent'] ?? false),
                    'actif' => true,
                    'ordre' => $def['ordre'] ?? 0,
                ]
            );
        }
        $this->info(sprintf('%d universités enregistrées.', count($universites)));

        $inserees = 0;
        $ignorees = 0;
        foreach ($donnees['quartiers'] as $q) {
            $universite = $universites[$q['universite']] ?? null;
            if (!$universite) {
                $ignorees++;
                continue;
            }

            Quartier::updateOrCreate(
                [
                    'universite_id' => $universite->id,
                    'nom' => $q['nom'],
                ],
                [
                    'arrondissement' => $q['arrondissement'] ?? null,
                    'source' => $q['source'] ?? 'officielle',
                ]
            );
            $inserees++;
        }

        $this->info(sprintf('%d quartiers traités (%d ignorés).', $inserees, $ignorees));

        return self::SUCCESS;
    }

    private function chargerLocal(): ?array
    {
        $chemin = database_path('data/quartiers.json');
        if (!is_file($chemin)) {
            $this->error('Fichier introuvable : '.$chemin);

            return null;
        }

        $donnees = json_decode((string) file_get_contents($chemin), true);

        return is_array($donnees) ? $donnees : null;
    }

    private function telecharger(): ?array
    {
        $universites = [];
        $quartiers = [];

        foreach (self::DEPARTEMENTS as $code => $depId) {
            $response = Http::timeout(45)->get('https://api-decoupage-benin.onrender.com/departements/'.$depId);

            if ($response->failed()) {
                if ($this->option('strict')) {
                    $this->error(sprintf('Département %d injoignable (%d).', $depId, $response->status()));

                    return null;
                }
                $this->warn(sprintf('Département %d injoignable, on garde le dump local.', $depId));
                continue;
            }

            $commune = $this->trouveCommune($response->json(), self::COMMUNES[$code]);
            if (!$commune) {
                $this->warn(sprintf('Commune %s introuvable dans le département %d.', self::COMMUNES[$code], $depId));
                continue;
            }

            foreach ($commune['arrondissements'] ?? [] as $arrondissement) {
                foreach ($arrondissement['quartiers'] ?? [] as $quartier) {
                    $quartiers[] = [
                        'universite' => $code,
                        'nom' => $this->capitalise($quartier['lib_quart']),
                        'arrondissement' => $this->capitalise($arrondissement['lib_arrond']),
                    ];
                }
            }

            $universites[] = [
                'code' => $code,
                'nom' => $this->nomUniversite($code),
                'ville' => $this->ville($code),
                'departement' => $this->departement($depId),
                'commune' => self::COMMUNES[$code],
                'accent' => $code === 'UAC',
                'ordre' => array_search($code, array_keys(self::DEPARTEMENTS), true) + 1,
            ];
        }

        if (count($universites) !== count(self::DEPARTEMENTS)) {
            $this->warn('Téléchargement incomplet, on complète avec le dump local.');
            $local = $this->chargerLocal();
            if ($local) {
                foreach ($local['quartiers'] as $q) {
                    $existe = false;
                    foreach ($quartiers as $deja) {
                        if ($deja['universite'] === $q['universite'] && $deja['nom'] === $q['nom']) {
                            $existe = true;
                            break;
                        }
                    }
                    if (!$existe) {
                        $quartiers[] = $q;
                    }
                }
                $codes = array_column($universites, 'code');
                foreach ($local['universites'] as $u) {
                    if (!in_array($u['code'], $codes, true)) {
                        $universites[] = $u;
                    }
                }
            }
        }

        return ['universites' => $universites, 'quartiers' => $quartiers];
    }

    private function trouveCommune(?array $departement, string $nom): ?array
    {
        foreach ($departement['communes'] ?? [] as $commune) {
            if (strcasecmp((string) ($commune['lib_com'] ?? ''), $nom) === 0) {
                return $commune;
            }
        }

        return null;
    }

    private function capitalise(string $valeur): string
    {
        return mb_convert_case(mb_strtolower(trim($valeur)), MB_CASE_TITLE, 'UTF-8');
    }

    private function nomUniversite(string $code): string
    {
        return match ($code) {
            'UAC' => "Université d'Abomey-Calavi",
            'UNA' => "Université Nationale d'Agriculture",
            'UNSTIM' => 'Université Nationale des Sciences, Technologies, Ingénierie et Mathématiques',
            'UP' => 'Université de Parakou',
            default => $code,
        };
    }

    private function ville(string $code): string
    {
        return match ($code) {
            'UAC' => 'Abomey-Calavi',
            'UNA' => 'Porto-Novo',
            'UNSTIM' => 'Abomey',
            'UP' => 'Parakou',
            default => '',
        };
    }

    private function departement(int $id): string
    {
        return match ($id) {
            3 => 'Atlantique',
            4 => 'Borgou',
            10 => 'Ouémé',
            12 => 'Zou',
            default => '',
        };
    }
}
