<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentIdentite extends Model
{
    protected $table = 'documents_identite';

    protected $fillable = [
        'user_id',
        'type_piece',
        'numero_piece',
        'photo_piece_url',
        'statut_verification',
        'commentaire_admin',
    ];

    protected function casts(): array
    {
        return [
            'commentaire_admin' => 'string',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
