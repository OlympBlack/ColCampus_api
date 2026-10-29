<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaAnnonce extends Model
{
    protected $table = 'medias_annonce';

    protected $fillable = [
        'annonce_id',
        'url_fichier',
        'est_panoramique_3d',
    ];

    protected function casts(): array
    {
        return [
            'est_panoramique_3d' => 'boolean',
        ];
    }

    public function annonce()
    {
        return $this->belongsTo(Annonce::class);
    }
}
