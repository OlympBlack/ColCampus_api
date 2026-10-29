<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortefeuillePublication extends Model
{
    protected $table = 'portefeuilles_publications';

    protected $fillable = [
        'user_id',
        'credits_gratuits',
        'credits_achetes',
        'credits_utilises',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculer les crédits restants
     */
    public function getCreditsRestantsAttribute(): int
    {
        return $this->credits_gratuits + $this->credits_achetes - $this->credits_utilises;
    }
}
