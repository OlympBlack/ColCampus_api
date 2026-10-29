<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageContact extends Model
{
    use HasFactory;

    protected $table = 'messages_contact';

    protected $fillable = [
        'user_id',
        'nom',
        'email',
        'telephone',
        'sujet',
        'message',
        'statut',
        'reponse_admin',
        'traite_par_user_id',
        'traite_le',
    ];

    protected $casts = [
        'traite_le' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function traitePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traite_par_user_id');
    }
}
