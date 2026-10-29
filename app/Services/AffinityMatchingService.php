<?php

namespace App\Services;

use App\Models\Annonce;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class AffinityMatchingService
{
    /**
     * Rank a collection of annonces for a specific user based on affinity.
     *
     * @param EloquentCollection $annonces
     * @param User|null $searcher
     * @return Collection
     */
    public function rankAnnonces(EloquentCollection $annonces, ?User $searcher): Collection
    {
        if (!$searcher || $searcher->role !== 'etudiant') {
            // For guests or non-students, sort by latest
            return $annonces->sortByDesc('created_at')->values();
        }

        $searcher->loadMissing('profilEtudiant');
        $searcherProfile = $searcher->profilEtudiant;

        if (!$searcherProfile) {
            return $annonces->sortByDesc('created_at')->values();
        }

        return $annonces->map(function (Annonce $annonce) use ($searcher, $searcherProfile) {
            $score = $this->calculateAffinityScore($annonce, $searcher, $searcherProfile);
            $annonce->affinity_score = $score;
            return $annonce;
        })->sort(function ($a, $b) {
            if ($a->affinity_score === $b->affinity_score) {
                return $b->created_at <=> $a->created_at; // Fallback to newest
            }
            return $b->affinity_score <=> $a->affinity_score; // Highest score first
        })->values();
    }

    /**
     * Calculate an affinity score between an Annonce (and its poster) and the searching student.
     */
    private function calculateAffinityScore(Annonce $annonce, User $searcher, $searcherProfile): int
    {
        $score = 0;

        // Base score: 100
        // We will add or subtract points based on affinities.

        // 1. Habitudes de vie & Cursus (only makes sense if the poster is also a student, e.g., colocation)
        if (in_array($annonce->type_annonce, ['PLACE_LIBRE_HOTE', 'RECHERCHE_BINOME'])) {
            $poster = $annonce->user;
            if ($poster && $poster->role === 'etudiant') {
                $poster->loadMissing('profilEtudiant');
                $posterProfile = $poster->profilEtudiant;

                if ($posterProfile) {
                    // a) Cursus Académique
                    if ($searcherProfile->universite_id && $searcherProfile->universite_id === $posterProfile->universite_id) {
                        $score += 25; // Same university
                    }
                    if ($searcherProfile->etablissement === $posterProfile->etablissement) {
                        $score += 20; // Same school
                    }
                    if ($searcherProfile->filiere === $posterProfile->filiere) {
                        $score += 15; // Same field of study
                    }
                    if ($searcherProfile->niveau === $posterProfile->niveau) {
                        $score += 10; // Same level
                    }

                    // b) Habitudes de vie (Preferences)
                    $searcherPrefs = $searcherProfile->preferences ?? [];
                    $posterPrefs = $posterProfile->preferences ?? [];
                    
                    if (is_array($searcherPrefs) && is_array($posterPrefs)) {
                        $commonPrefs = array_intersect($searcherPrefs, $posterPrefs);
                        // +5 points for each shared preference
                        $score += count($commonPrefs) * 10;
                    }

                    // c) Sexe
                    if ($searcherProfile->sexe === $posterProfile->sexe) {
                        $score += 25; // Often very important for roommates
                    } else {
                        $score -= 10;
                    }
                }
            }
        } else {
            // For CHAMBRE_VIDE, lifestyle doesn't matter much. Give a baseline score so they aren't completely buried.
            $score += 30; 
        }

        // 2. Budget Matching (Applies to all types of annonces)
        if ($searcherProfile->budget_max) {
            $budgetMax = (int) $searcherProfile->budget_max;
            $loyer = (int) $annonce->loyer;

            if ($loyer <= $budgetMax) {
                // If it's within budget, give a high score, especially if it's close to the budget (efficient use)
                // or very cheap. Let's just give a flat +40 if within budget.
                $score += 40;

                // Bonus if it's significantly under budget
                if ($loyer <= $budgetMax * 0.8) {
                    $score += 10; 
                }
            } else {
                // Penalize if over budget
                $overage = $loyer - $budgetMax;
                if ($overage <= 5000) {
                    $score -= 10; // Slightly over
                } elseif ($overage <= 10000) {
                    $score -= 30; // Moderately over
                } else {
                    $score -= 60; // Way over budget
                }
            }
        }

        return $score;
    }
}
