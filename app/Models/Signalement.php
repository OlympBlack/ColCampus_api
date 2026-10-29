<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Signalement extends Model
{
    use HasFactory;

    protected $table = 'signalements';

    protected $fillable = [
        'annonce_id',
        'user_id',
        'nom',
        'email',
        'telephone',
        'motif',
        'description',
        'statut',
        'decision',
        'notes_admin',
        'traite_par_user_id',
        'traite_le',
    ];

    protected $casts = [
        'traite_le' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function annonce(): BelongsTo
    {
        return $this->belongsTo(Annonce::class, 'annonce_id')->with(['user', 'medias']);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function traitePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traite_par_user_id');
    }
}
