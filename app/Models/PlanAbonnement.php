<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanAbonnement extends Model
{
    protected $table = 'plans_abonnement';

    protected $fillable = [
        'nom_plan',
        'prix',
        'duree_mois',
        'limite_annonces',
    ];

    protected function casts(): array
    {
        return [
            'prix' => 'decimal:2',
            'duree_mois' => 'integer',
            'limite_annonces' => 'integer',
        ];
    }

    public function abonnements()
    {
        return $this->hasMany(Abonnement::class, 'plan_id');
    }
}
