<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilBoutique extends Model
{
    use HasFactory;

    protected $table = 'profils_boutiques';

    protected $fillable = [
        'user_id',
        'ifu_rccm',
        'nom_boutique',
        'categorie',
        'adresse_physique',
        'logo_url',
        'ifu_doc_path',
        'rccm_doc_path'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
