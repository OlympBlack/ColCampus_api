<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionCredit extends Model
{
    protected $table = 'transactions_credits';

    protected $fillable = [
        'user_id',
        'type',
        'montant',
        'source_type',
        'source_id',
        'description',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
