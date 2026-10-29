<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForfaitPublication extends Model
{
    protected $table = 'forfaits_publications';

    protected $fillable = [
        'titre',
        'description',
        'prix',
        'nombre_publications',
        'est_actif',
        'est_populaire',
        'ordre_affichage',
    ];

    protected $casts = [
        'prix' => 'decimal:2',
        'est_actif' => 'boolean',
        'est_populaire' => 'boolean',
        'ordre_affichage' => 'integer',
        'nombre_publications' => 'integer',
    ];

    public function achats(): HasMany
    {
        return $this->hasMany(AchatForfait::class, 'forfait_id');
    }
}
