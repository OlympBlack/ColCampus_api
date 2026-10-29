<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AchatForfait extends Model
{
    protected $table = 'achats_forfaits';

    protected $fillable = [
        'user_id',
        'forfait_id',
        'nombre_publications',
        'montant',
        'statut_paiement',
        'methode_paiement',
        'reference_transaction',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'nombre_publications' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function forfait(): BelongsTo
    {
        return $this->belongsTo(ForfaitPublication::class, 'forfait_id');
    }
}
